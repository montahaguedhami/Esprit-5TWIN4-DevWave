<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Tests\TestCase;

/**
 * Cas limites de la connexion Google (GoogleAuthController) : configuration
 * absente, erreurs renvoyées par Google, création d'un compte citoyen et
 * refus de liaison. Socialite est entièrement simulé : aucun appel réseau.
 */
class GoogleAuthEdgeCasesTest extends TestCase
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
     |  Configuration absente
     |_______________________________________________________________________*/

    public function test_sign_in_is_refused_when_google_is_not_configured(): void
    {
        config()->set('services.google.client_id', null);
        Socialite::shouldReceive('driver')->never();

        $this->get(route('auth.google'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'GOOGLE_CLIENT_ID'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'GOOGLE_CLIENT_SECRET'));
    }

    public function test_link_is_refused_when_google_is_not_configured(): void
    {
        config()->set('services.google.client_secret', '');
        Socialite::shouldReceive('driver')->never();
        $this->actingAs($this->makeUser(Role::CITOYEN));

        $this->get(route('profile.google.link'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('error')
            ->assertSessionMissing('google_oauth_intent');
    }

    public function test_production_does_not_reveal_configuration_details(): void
    {
        config()->set('services.google.client_id', null);
        $this->app['env'] = 'production';

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'La connexion Google est momentanément indisponible.');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Erreurs renvoyées par Google
     |_______________________________________________________________________*/

    public function test_cancelled_consent_returns_to_the_login_page(): void
    {
        Socialite::shouldReceive('driver')->never();

        $this->get(route('auth.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'La connexion avec Google a été annulée.');

        $this->assertGuest();
    }

    public function test_expired_oauth_state_is_reported(): void
    {
        $this->fakeGoogleFailure(new InvalidStateException());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'La session de connexion Google a expiré. Réessayez.');
    }

    public function test_google_outage_is_reported_without_details(): void
    {
        $this->fakeGoogleFailure(new \RuntimeException('cURL error 28: timeout'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Impossible de communiquer avec Google pour le moment. Réessayez.');

        $this->assertGuest();
    }

    public function test_identity_without_id_is_refused(): void
    {
        $this->fakeGoogle('', 'sans-id@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Google n\'a pas retourné d\'identifiant valide.');

        $this->assertDatabaseMissing('users', ['email' => 'sans-id@test.local']);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Comptes Google existants
     |_______________________________________________________________________*/

    public function test_deactivated_google_account_cannot_sign_in(): void
    {
        $user = $this->makeUser(Role::CITOYEN, provider: 'google', providerId: 'gid-off', isActive: false);
        $this->fakeGoogle('gid-off', $user->email);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Ce compte a été désactivé par un administrateur.');

        $this->assertGuest();
        $this->assertFalse($user->fresh()->is_active, 'Un compte désactivé n\'est jamais réactivé.');
    }

    public function test_google_account_without_role_cannot_sign_in(): void
    {
        $user = $this->makeUser(null, provider: 'google', providerId: 'gid-sans-role');
        $this->fakeGoogle('gid-sans-role', $user->email);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Aucun rôle assigné à ce compte. Contactez l\'administrateur.');

        $this->assertGuest();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Nouveau compte
     |_______________________________________________________________________*/

    public function test_new_google_identity_creates_a_citizen_account(): void
    {
        Notification::fake();
        $this->fakeGoogle('gid-nouveau', 'nouveau.google@test.local', name: 'Rim Gharbi', avatar: 'https://lh3.example/photo.jpg');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('citizen.dashboard'));

        $user = User::where('email', 'nouveau.google@test.local')->firstOrFail();
        $this->assertSame('Rim Gharbi', $user->name);
        $this->assertSame('google', $user->provider);
        $this->assertSame('gid-nouveau', $user->provider_id);
        $this->assertSame('https://lh3.example/photo.jpg', $user->avatar);
        $this->assertTrue($user->is_active);
        // Un compte social est toujours un simple citoyen.
        $this->assertSame([Role::CITOYEN], $user->getRoleSlugs());
        // Mot de passe aléatoire, haché : le compte social ne s'ouvre pas par mot de passe.
        $this->assertTrue(Hash::isHashed($user->password));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(Role::CITOYEN, session('user.role'));
        $this->assertSame($user->id, session('user.id'));

        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'social_register']);
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_new_account_name_falls_back_to_the_email_prefix(): void
    {
        Notification::fake();
        $this->fakeGoogle('gid-sans-nom', 'sans.nom@test.local', name: '');

        $this->get(route('auth.google.callback'))->assertRedirect(route('citizen.dashboard'));

        $this->assertSame('sans.nom', User::where('email', 'sans.nom@test.local')->value('name'));
    }

    public function test_google_identity_without_email_cannot_create_an_account(): void
    {
        $avant = User::count();
        $this->fakeGoogle('gid-sans-email', null);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'ne fournit pas d\'adresse email'));

        $this->assertSame($avant, User::count());
        $this->assertGuest();
    }

    public function test_account_creation_is_refused_when_the_citizen_role_is_missing(): void
    {
        Role::where('slug', Role::CITOYEN)->delete();
        $this->fakeGoogle('gid-sans-role-citoyen', 'pas.configure@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Le système n\'est pas encore configuré. Contactez l\'administrateur.');

        $this->assertDatabaseMissing('users', ['email' => 'pas.configure@test.local']);
    }

    public function test_welcome_mail_failure_does_not_block_the_sign_up(): void
    {
        // Panne SMTP simulée sur l'email de bienvenue.
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP indisponible'));
        $this->fakeGoogle('gid-smtp', 'smtp.down@test.local');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('citizen.dashboard'));

        $user = User::where('email', 'smtp.down@test.local')->firstOrFail();
        $this->assertAuthenticatedAs($user);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Liaison depuis le profil
     |_______________________________________________________________________*/

    public function test_link_intent_with_an_expired_session_asks_to_sign_in_again(): void
    {
        $this->fakeGoogle('gid-lien', 'lien@test.local');

        $this->withSession(['google_oauth_intent' => 'link'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Votre session a expiré. Connectez-vous pour lier votre compte Google.')
            ->assertSessionMissing('google_oauth_intent');

        $this->assertDatabaseMissing('users', ['email' => 'lien@test.local']);
    }

    public function test_google_identity_linked_elsewhere_cannot_be_linked_again(): void
    {
        $this->makeUser(Role::CITOYEN, provider: 'google', providerId: 'gid-pris');
        $user = $this->makeUser(Role::CITOYEN);
        $this->actingAs($user);

        $this->fakeGoogle('gid-pris', $user->email);

        $this->withSession(['google_oauth_intent' => 'link'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('error', 'Ce compte Google est déjà lié à un autre utilisateur AquaSecure.');

        $this->assertNull($user->fresh()->provider_id);
    }

    public function test_deactivated_account_cannot_link_google(): void
    {
        $user = $this->makeUser(Role::CITOYEN, isActive: false);
        $this->actingAs($user);
        $this->fakeGoogle('gid-inactif', $user->email);

        $this->withSession(['google_oauth_intent' => 'link'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', 'Ce compte a été désactivé par un administrateur.');

        $this->assertNull($user->fresh()->provider);
    }

    public function test_link_works_when_google_hides_the_email(): void
    {
        $user = $this->makeUser(Role::CITOYEN);
        $this->actingAs($user);
        $this->fakeGoogle('gid-email-masque', null);

        $this->withSession(['google_oauth_intent' => 'link'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Votre compte Google a été lié avec succès.');

        $this->assertDatabaseHas('users', [
            'id'          => $user->id,
            'provider'    => 'google',
            'provider_id' => 'gid-email-masque',
        ]);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    private function makeUser(
        ?string $roleSlug,
        ?string $provider = null,
        ?string $providerId = null,
        bool $isActive = true,
    ): User {
        $user = User::create([
            'name'        => 'Compte Google Test',
            'email'       => uniqid('google.edge.', true).'@test.local',
            'password'    => 'Secret#Google2026',
            'is_active'   => $isActive,
            'provider'    => $provider,
            'provider_id' => $providerId,
        ]);

        if ($roleSlug !== null) {
            $user->assignRole($roleSlug);
        }

        return $user;
    }

    private function fakeGoogle(string $providerId, ?string $email, string $name = 'Google Tester', ?string $avatar = null): void
    {
        $socialUser = new class($providerId, $email, $name, $avatar)
        {
            public function __construct(
                private readonly string $id,
                private readonly ?string $email,
                private readonly string $name,
                private readonly ?string $avatar,
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
                return $this->name;
            }

            public function getAvatar(): ?string
            {
                return $this->avatar;
            }

            public function getRaw(): array
            {
                return ['email' => $this->email, 'email_verified' => true];
            }
        };

        $provider = \Mockery::mock();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function fakeGoogleFailure(\Throwable $exception): void
    {
        $provider = \Mockery::mock();
        $provider->shouldReceive('user')->andThrow($exception);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
