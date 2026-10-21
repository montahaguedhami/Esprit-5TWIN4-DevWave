<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Gestion du compte depuis la page profil (ProfileController) :
 * informations personnelles, mot de passe, photo et suppression du compte.
 */
class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Actuel#Secret2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Affichage
     |_______________________________________________________________________*/

    public function test_guest_is_sent_to_the_login_page(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('auth.login'));
        $this->get(route('profile.edit'))->assertRedirect(route('auth.login'));
        $this->patch(route('profile.update'), ['name' => 'X'])->assertRedirect(route('auth.login'));
    }

    public function test_profile_pages_show_the_connected_user(): void
    {
        $user = $this->loginAs(Role::CITOYEN, name: 'Salma Trabelsi');

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertViewIs('profile.show')
            ->assertViewHas('user', fn (User $u) => $u->is($user))
            ->assertSee('Salma Trabelsi');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertViewIs('profile.edit')
            ->assertViewHas('user', fn (User $u) => $u->is($user));
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Informations personnelles
     |_______________________________________________________________________*/

    public function test_name_can_be_updated_without_new_verification(): void
    {
        Notification::fake();
        $user = $this->loginAs(Role::CITOYEN);

        $this->patch(route('profile.update'), [
            'name'  => 'Nouveau Nom',
            'email' => $user->email,
        ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Informations mises à jour avec succès.');

        $user->refresh();
        $this->assertSame('Nouveau Nom', $user->name);
        $this->assertNotNull($user->email_verified_at, 'L\'email inchangé reste vérifié.');
        $this->assertAuthenticatedAs($user);
        Notification::assertNothingSent();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'profile_updated',
        ]);
    }

    public function test_changing_the_email_requires_a_new_verification(): void
    {
        Notification::fake();
        $user = $this->loginAs(Role::CITOYEN);

        $this->patch(route('profile.update'), [
            'name'  => $user->name,
            'email' => 'nouvelle.adresse@test.local',
        ])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('success', 'Email mis à jour. Un lien de vérification a été envoyé à votre nouvelle adresse.');

        $user->refresh();
        $this->assertSame('nouvelle.adresse@test.local', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertGuest();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_profile_update_is_validated(): void
    {
        $user = $this->loginAs(Role::CITOYEN);

        // Adresse déjà utilisée par un autre compte.
        $this->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name'  => $user->name,
                'email' => 'admin@aquasecure.tn',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        // Nom trop court et email invalide.
        $this->from(route('profile.edit'))
            ->patch(route('profile.update'), ['name' => 'A', 'email' => 'invalide'])
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertSame($user->email, $user->fresh()->email);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Mot de passe
     |_______________________________________________________________________*/

    public function test_password_change_requires_the_current_password(): void
    {
        $user = $this->loginAs(Role::CITOYEN);
        $hashAvant = $user->password;

        $this->from(route('profile.show'))
            ->patch(route('profile.password.update'), [
                'current_password'      => 'Mauvais#Secret2026',
                'password'              => 'Nouveau#Secret2026',
                'password_confirmation' => 'Nouveau#Secret2026',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])
            ->assertSessionHas('tab', 'password');

        $this->assertSame($hashAvant, $user->fresh()->password);
    }

    public function test_new_password_must_be_strong(): void
    {
        $this->loginAs(Role::CITOYEN);

        $cas = [
            'Ab1!'         => 'Le mot de passe doit contenir au moins 8 caractères.',
            'minuscule1!'  => 'Le mot de passe doit contenir des majuscules et minuscules.',
            'SansChiffre!' => 'Le mot de passe doit contenir au moins un chiffre.',
            'SansSymbole1' => 'Le mot de passe doit contenir au moins un caractère spécial.',
        ];

        foreach ($cas as $password => $message) {
            $this->from(route('profile.show'))
                ->patch(route('profile.password.update'), [
                    'current_password'      => self::PASSWORD,
                    'password'              => $password,
                    'password_confirmation' => $password,
                ])
                ->assertSessionHasErrors('password');

            $this->assertContains($message, session('errors')->get('password'), "Mot de passe testé : {$password}");
        }
    }

    public function test_password_is_changed_with_the_right_current_password(): void
    {
        $user = $this->loginAs(Role::CITOYEN);

        $this->patch(route('profile.password.update'), [
            'current_password'      => self::PASSWORD,
            'password'              => 'Nouveau#Secret2026',
            'password_confirmation' => 'Nouveau#Secret2026',
        ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Mot de passe mis à jour avec succès.')
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Nouveau#Secret2026', $user->fresh()->password));
        $this->assertFalse(Hash::check(self::PASSWORD, $user->fresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'password_changed',
        ]);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Photo de profil
     |_______________________________________________________________________*/

    public function test_avatar_upload_replaces_the_previous_picture(): void
    {
        Storage::fake('public');
        $user = $this->loginAs(Role::CITOYEN);

        Storage::disk('public')->put('avatars/ancienne.png', 'ancienne');
        $user->forceFill(['avatar' => 'avatars/ancienne.png'])->save();

        $this->patch(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('photo.png', 120, 120),
        ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Photo de profil mise à jour.');

        $chemin = $user->fresh()->avatar;
        $this->assertStringStartsWith('avatars/', $chemin);
        $this->assertNotSame('avatars/ancienne.png', $chemin);
        Storage::disk('public')->assertExists($chemin);
        Storage::disk('public')->assertMissing('avatars/ancienne.png');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'avatar_updated',
        ]);
    }

    public function test_avatar_must_be_a_small_image(): void
    {
        Storage::fake('public');
        $user = $this->loginAs(Role::CITOYEN);

        $this->from(route('profile.show'))
            ->patch(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['avatar' => 'Le fichier doit être une image.']);

        $this->from(route('profile.show'))
            ->patch(route('profile.avatar.update'), [
                'avatar' => UploadedFile::fake()->image('grande.jpg')->size(3000),
            ])
            ->assertSessionHasErrors(['avatar' => 'L\'image ne doit pas dépasser 2 Mo.']);

        $this->from(route('profile.show'))
            ->patch(route('profile.avatar.update'), [])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_avatar_can_be_removed(): void
    {
        Storage::fake('public');
        $user = $this->loginAs(Role::CITOYEN);

        Storage::disk('public')->put('avatars/moi.png', 'image');
        $user->forceFill(['avatar' => 'avatars/moi.png'])->save();

        $this->delete(route('profile.avatar.delete'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Photo de profil supprimée.');

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing('avatars/moi.png');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Suppression du compte
     |_______________________________________________________________________*/

    public function test_account_deletion_requires_the_password(): void
    {
        $user = $this->loginAs(Role::CITOYEN);

        $this->from(route('profile.show'))
            ->delete(route('profile.destroy'), [])
            ->assertSessionHasErrors(['password' => 'Veuillez confirmer votre mot de passe pour supprimer le compte.']);

        $this->from(route('profile.show'))
            ->delete(route('profile.destroy'), ['password' => 'Mauvais#Secret2026'])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHasErrors(['password' => 'Mot de passe incorrect.'])
            ->assertSessionHas('tab', 'danger');

        $this->assertModelExists($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_last_administrator_cannot_delete_their_account(): void
    {
        $admin = User::where('email', 'admin@aquasecure.tn')->firstOrFail();
        $this->assertTrue($admin->isLastAdministrator(), 'Pré-condition : un seul administrateur actif.');
        $this->actingAs($admin);

        $this->from(route('profile.show'))
            ->delete(route('profile.destroy'), ['password' => 'password123'])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('error', 'Impossible de supprimer le dernier compte administrateur du système.');

        $this->assertModelExists($admin);
    }

    public function test_user_can_delete_their_own_account(): void
    {
        Storage::fake('public');
        $user = $this->loginAs(Role::CITOYEN);

        Storage::disk('public')->put('avatars/moi.png', 'image');
        $user->forceFill(['avatar' => 'avatars/moi.png'])->save();

        $this->delete(route('profile.destroy'), ['password' => self::PASSWORD])
            ->assertRedirect(route('landing'))
            ->assertSessionHas('success', 'Votre compte a été supprimé avec succès.');

        $this->assertModelMissing($user);
        $this->assertGuest();
        Storage::disk('public')->assertMissing('avatars/moi.png');

        // La trace d'audit survit au compte (user_id remis à NULL, email conservé).
        $this->assertDatabaseHas('audit_logs', [
            'action'     => 'account_deleted',
            'user_email' => $user->email,
        ]);
    }

    public function test_an_administrator_can_leave_when_another_one_remains(): void
    {
        $admin = $this->loginAs(Role::ADMINISTRATEUR);

        $this->delete(route('profile.destroy'), ['password' => self::PASSWORD])
            ->assertRedirect(route('landing'));

        $this->assertModelMissing($admin);
        $this->assertTrue(User::where('email', 'admin@aquasecure.tn')->exists());
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    private function loginAs(string $roleSlug, string $name = 'Compte Profil'): User
    {
        $user = User::factory()->create([
            'name'      => $name,
            'email'     => uniqid('profil.', true).'@test.local',
            'password'  => self::PASSWORD,
            'is_active' => true,
        ]);
        $user->assignRole($roleSlug);

        $this->actingAs($user);
        $this->withSession(['user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $roleSlug,
            'role_key' => $roleSlug,
            'roles'    => [$roleSlug],
        ]]);

        return $user;
    }
}
