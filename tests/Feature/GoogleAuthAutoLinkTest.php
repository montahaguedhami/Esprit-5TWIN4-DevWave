<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

/**
 * Connexion automatique Google → compte local partageant la même adresse.
 *
 * L'auto-link n'est autorisé que lorsque les DEUX côtés prouvent la propriété
 * de l'adresse : Google renvoie `email_verified: true` ET le compte local a
 * vérifié son adresse via le lien signé de vérification (`email_verified_at`).
 * Toute autre situation conserve le refus historique (connexion par mot de
 * passe puis liaison explicite depuis le profil).
 */
class GoogleAuthAutoLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);

        config()->set('services.google.client_id', 'test-client-id');
        config()->set('services.google.client_secret', 'test-client-secret');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Les deux emails vérifiés → connexion automatique
     |_______________________________________________________________________*/

    public function test_un_compte_local_verifie_est_connecte_et_lie_automatiquement(): void
    {
        $local = $this->makeLocalUser(
            Role::CITOYEN,
            email: 'delivrance.test@test.local',
            emailVerified: true,
        );

        $this->fakeGoogle('gid-auto-1', 'delivrance.test@test.local', emailVerified: true);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('citizen.dashboard'));

        // L'utilisateur est connecté sous son compte local existant.
        $this->assertTrue(auth()->check(), 'La connexion automatique doit ouvrir une session.');
        $this->assertSame($local->id, auth()->id());

        // L'identité Google est désormais attachée à son compte local.
        $this->assertDatabaseHas('users', [
            'id'          => $local->id,
            'provider'    => 'google',
            'provider_id' => 'gid-auto-1',
        ]);

        // La liaison automatique est tracée dans le journal d'audit.
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $local->id,
            'action'  => 'social_link',
        ]);
    }

    public function test_le_role_du_compte_local_est_preserve_par_la_connexion_automatique(): void
    {
        // Un gestionnaire local ne doit pas redevenir citoyen via Google.
        $manager = $this->makeLocalUser(Role::MANAGER, email: 'chef.projets@test.local');

        $this->fakeGoogle('gid-auto-2', 'chef.projets@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('manager.dashboard'));

        $this->assertSame(Role::MANAGER, session('user.role'));
        $this->assertFalse(
            $manager->fresh()->hasRole(Role::CITOYEN),
            'Le rôle du compte local ne doit pas être remplacé par citoyen.',
        );
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Refus préservés : chaque garde-fou reste actif
     |_______________________________________________________________________*/

    public function test_l_auto_link_est_refuse_si_google_ne_verifie_pas_l_email(): void
    {
        $local = $this->makeLocalUser(Role::CITOYEN, email: 'cote-google-non-verifie@test.local');

        // Google n'a pas confirmé l'adresse (compte non vérifié côté Google).
        $this->fakeGoogle('gid-auto-3', 'cote-google-non-verifie@test.local', emailVerified: false);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertNull($local->fresh()->provider, 'Le compte local ne doit pas être lié.');
    }

    public function test_l_auto_link_est_refuse_si_le_compte_local_n_a_pas_verifie_son_email(): void
    {
        $local = $this->makeLocalUser(
            Role::CITOYEN,
            email: 'cote-local-non-verifie@test.local',
            emailVerified: false,
        );

        $this->fakeGoogle('gid-auto-4', 'cote-local-non-verifie@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertNull($local->fresh()->provider);
    }

    public function test_un_compte_desactive_ne_est_pas_li_ni_connecte(): void
    {
        $local = $this->makeLocalUser(
            Role::CITOYEN,
            email: 'desactive.test@test.local',
            isActive: false,
        );

        $this->fakeGoogle('gid-auto-5', 'desactive.test@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertNull(
            $local->fresh()->provider,
            'Un compte désactivé ne doit jamais être lié automatiquement.',
        );
    }

    public function test_un_provider_deja_attache_ne_est_pas_ecrase(): void
    {
        // Le compte est déjà lié à une autre identité : ne pas l'écraser,
        // même avec des emails vérifiés des deux côtés.
        $local = $this->makeLocalUser(
            Role::CITOYEN,
            email: 'deja-lie.test@test.local',
            provider: 'facebook',
            providerId: 'fb-id-777',
        );

        $this->fakeGoogle('gid-auto-6', 'deja-lie.test@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id'          => $local->id,
            'provider'    => 'facebook',
            'provider_id' => 'fb-id-777',
        ]);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    private function makeLocalUser(
        string $roleSlug,
        ?string $email = null,
        bool $emailVerified = true,
        bool $isActive = true,
        ?string $provider = null,
        ?string $providerId = null,
    ): User {
        $role = Role::where('slug', $roleSlug)->firstOrFail();

        $user = User::create([
            'name'       => 'Compte Local '.ucfirst($roleSlug),
            'email'      => $email ?: uniqid('local.', true).'@test.local',
            'password'   => bcrypt('password123'),
            'is_active'  => $isActive,
            'provider'   => $provider,
            'provider_id'=> $providerId,
        ]);

        // email_verified_at n'est pas dans $fillable : passage obligé par
        // forceFill (comme le fait le flux de vérification en base réelle).
        $user->forceFill(['email_verified_at' => $emailVerified ? now() : null])->save();

        $user->assignRole($role);

        return $user;
    }

    private function fakeGoogle(string $providerId, ?string $email, bool $emailVerified = true): void
    {
        $socialUser = new class($providerId, $email, $emailVerified)
        {
            public function __construct(
                private readonly string $id,
                private readonly ?string $email,
                private readonly bool $emailVerified,
            ) {}

            public function getId(): string
            {
                return $this->id;
            }

            public function getEmail(): ?string
            {
                return $this->email;
            }

            public function getName(): string
            {
                return 'Google Tester';
            }

            public function getAvatar(): ?string
            {
                return null;
            }

            public function getRaw(): array
            {
                return ['email' => $this->email, 'email_verified' => $this->emailVerified];
            }
        };

        $provider = \Mockery::mock();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
