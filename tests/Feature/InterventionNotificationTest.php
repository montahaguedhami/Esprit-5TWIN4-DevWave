<?php

namespace Tests\Feature;

use App\Enums\InfrastructureType;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\InterventionAssignee;
use App\Notifications\InterventionModifiee;
use App\Notifications\InterventionTerminee;
use App\Services\PreventiveMaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InterventionNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $compteTechnicien;

    private Technicien $technicien;

    private User $gestionnaire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);

        $this->compteTechnicien = $this->userWithRole(Role::TECHNICIEN, ['name' => 'Sami Gharbi']);
        $this->technicien = Technicien::factory()->create([
            'nom' => 'Sami Gharbi',
            'specialite' => 'Pompage',
            'disponibilite' => 'Disponible',
            'user_id' => $this->compteTechnicien->id,
        ]);
        $this->gestionnaire = $this->userWithRole(Role::MANAGER, ['name' => 'Ines Mansouri']);
    }

    private function userWithRole(string $roleSlug, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true], $attrs));
        $user->assignRole($roleSlug);

        return $user->refresh()->load('roles');
    }

    private function loginAs(User $user): self
    {
        return $this->actingAs($user)->withSession([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->primary_role,
                'roles' => $user->getRoleSlugs(),
            ],
        ]);
    }

    private function intervention(array $attributs = []): Intervention
    {
        return Intervention::factory()->for($this->technicien)->create(array_merge([
            'date' => today()->addDays(2)->toDateString(),
            'statut' => 'Planifiée',
            'description' => 'Remplacement de la pompe de relevage',
        ], $attributs));
    }

    // ── Déclenchement ─────────────────────────────────────────────────────────

    public function test_technician_is_notified_by_database_and_mail_when_assigned(): void
    {
        Notification::fake();

        $intervention = $this->intervention();

        Notification::assertSentTo(
            $this->compteTechnicien,
            InterventionAssignee::class,
            fn (InterventionAssignee $n, array $canaux) => $n->intervention->is($intervention) && $canaux === ['database', 'mail']
        );
        Notification::assertNotSentTo($this->gestionnaire, InterventionAssignee::class);
    }

    public function test_reassigning_notifies_the_new_technician(): void
    {
        $autreCompte = $this->userWithRole(Role::TECHNICIEN);
        $autre = Technicien::factory()->create(['user_id' => $autreCompte->id, 'disponibilite' => 'Disponible']);
        $intervention = $this->intervention();

        Notification::fake();
        $intervention->update(['technicien_id' => $autre->id]);

        Notification::assertSentTo($autreCompte, InterventionAssignee::class);
        Notification::assertNotSentTo($this->compteTechnicien, InterventionAssignee::class);
    }

    public function test_rescheduling_and_cancelling_notify_the_technician(): void
    {
        $intervention = $this->intervention();
        $ancienneDate = $intervention->date->copy();

        Notification::fake();
        $intervention->update(['date' => today()->addDays(5)->toDateString()]);

        Notification::assertSentTo($this->compteTechnicien, InterventionModifiee::class,
            fn (InterventionModifiee $n) => ! $n->estAnnulee() && $n->ancienneDate->isSameDay($ancienneDate));

        $intervention->update(['statut' => 'Annulée']);

        Notification::assertSentTo($this->compteTechnicien, InterventionModifiee::class,
            fn (InterventionModifiee $n) => $n->estAnnulee());
    }

    public function test_managers_are_notified_when_an_intervention_is_finished(): void
    {
        $intervention = $this->intervention(['date' => today()->toDateString()]);

        Notification::fake();
        $intervention->update(['statut' => 'Terminée']);

        Notification::assertSentTo($this->gestionnaire, InterventionTerminee::class);
        Notification::assertNotSentTo($this->compteTechnicien, InterventionTerminee::class);
    }

    public function test_editing_the_description_sends_nothing(): void
    {
        $intervention = $this->intervention();

        Notification::fake();
        $intervention->update(['description' => 'Remplacement de la pompe — pièce commandée']);

        Notification::assertNothingSent();
    }

    public function test_technician_without_account_is_skipped_silently(): void
    {
        Notification::fake();

        $sansCompte = Technicien::factory()->create(['user_id' => null]);
        Intervention::factory()->for($sansCompte)->create(['date' => today()->addDay()->toDateString(), 'statut' => 'Planifiée']);

        Notification::assertNothingSent();
        $this->assertDatabaseCount('interventions', 1);
    }

    public function test_preventive_planning_notifies_the_assigned_technician(): void
    {
        Notification::fake();

        Infrastructure::factory()->create([
            'zone_id' => Zone::create(['nom' => 'Zone Nord', 'localisation' => 'Bizerte'])->id,
            'type' => InfrastructureType::StationPompage,
            'date_prochaine_maintenance' => today()->addDays(2)->toDateString(),
        ]);

        app(PreventiveMaintenanceService::class)->planifier();

        Notification::assertSentTo($this->compteTechnicien, InterventionAssignee::class,
            fn (InterventionAssignee $n) => $n->intervention->estPreventive());
    }

    public function test_a_failing_mail_server_never_blocks_the_intervention(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        $intervention = $this->intervention();

        $this->assertDatabaseHas('interventions', ['id' => $intervention->id]);
        // Le canal base de données passe avant le mail : la notification est quand même enregistrée
        $this->assertSame(1, $this->compteTechnicien->notifications()->count());
    }

    // ── Cloche et page ────────────────────────────────────────────────────────

    public function test_bell_shows_real_unread_notifications(): void
    {
        $intervention = $this->intervention();

        $this->loginAs($this->compteTechnicien)
            ->get(route('technician.interventions.index'))
            ->assertOk()
            ->assertSee('Nouvelle intervention corrective')
            ->assertSee('1 non lues')
            ->assertSee(route('notifications.ouvrir', $this->compteTechnicien->notifications()->first()->id))
            ->assertDontSee('Mise à jour système disponible'); // ancienne donnée factice
    }

    public function test_opening_a_notification_marks_it_read_and_redirects(): void
    {
        $intervention = $this->intervention();
        $notification = $this->compteTechnicien->notifications()->first();

        $this->loginAs($this->compteTechnicien)
            ->get(route('notifications.ouvrir', $notification->id))
            ->assertRedirect(route('technician.interventions.show', $intervention->id));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_and_notifications_page(): void
    {
        $this->intervention();
        $this->intervention(['date' => today()->addDays(3)->toDateString()]);

        $this->loginAs($this->compteTechnicien)
            ->get(route('notifications.index', ['filtre' => 'non-lues']))
            ->assertOk()
            ->assertSee('Toutes les notifications')
            ->assertSee('Tout marquer comme lu');

        $this->loginAs($this->compteTechnicien)
            ->postJson(route('notifications.tout-lire'))
            ->assertOk();

        $this->assertSame(0, $this->compteTechnicien->unreadNotifications()->count());
    }

    public function test_users_cannot_open_someone_elses_notification(): void
    {
        $this->intervention();
        $notification = $this->compteTechnicien->notifications()->first();

        $this->loginAs($this->gestionnaire)
            ->get(route('notifications.ouvrir', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
