<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

/**
 * The Google callback used to decide between "sign in" and "link an account"
 * by looking at whether any session already carried a user id. Every visit to
 * /auth/google therefore ended in a link, and the visitor was dropped on the
 * profile page instead of the dashboard of their role.
 *
 * The intent is now carried explicitly: the login button signs in, and only
 * the profile route may link.
 */
class GoogleAuthRedirectTest extends TestCase
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
     |  Sign in must always end on the dashboard of the role
     |_______________________________________________________________________*/

    public function test_google_sign_in_reaches_the_citizen_dashboard(): void
    {
        $user = $this->makeGoogleUser(Role::CITOYEN);

        $this->fakeGoogle($user->provider_id, $user->email);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('citizen.dashboard'));

        $this->assertTrue(auth()->check(), 'La connexion Google doit ouvrir une session.');
        $this->assertSame($user->id, auth()->id());
    }

    /**
     * The regression itself: an already open session used to turn the visit
     * into an account link, which redirects to the profile page.
     */
    public function test_google_sign_in_reaches_the_dashboard_even_with_an_open_session(): void
    {
        $user = $this->makeGoogleUser(Role::CITOYEN);

        // A session is already open — exactly the situation that used to
        // trigger the "link" branch.
        $this->actingAs($user);
        $this->withSession(['user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => Role::CITOYEN,
            'role_key' => Role::CITOYEN,
            'roles'    => [Role::CITOYEN],
        ]]);

        $this->fakeGoogle($user->provider_id, $user->email);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('citizen.dashboard'))
            ->assertDontSee(route('profile.show'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'social_login',
        ]);
    }

    public function test_google_sign_in_follows_the_role_of_the_user(): void
    {
        $manager = $this->makeGoogleUser(Role::MANAGER);

        $this->fakeGoogle($manager->provider_id, $manager->email);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('manager.dashboard'));

        $this->assertSame(Role::MANAGER, session('user.role'));
    }

    public function test_technician_signed_in_with_google_reaches_the_technician_dashboard(): void
    {
        $technician = $this->makeGoogleUser(Role::TECHNICIEN);

        $this->fakeGoogle($technician->provider_id, $technician->email);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('technician.dashboard'));
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Linking still works, but only when it is asked for
     |_______________________________________________________________________*/

    public function test_the_link_route_links_the_account_and_returns_to_the_profile(): void
    {
        $user = $this->makeGoogleUser(Role::CITOYEN, provider: null, providerId: null);

        $this->actingAs($user);

        $this->fakeGoogle('google-id-42', $user->email);

        // Only this entry point may link.
        $this->get(route('profile.google.link'))->assertRedirect();
        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('profile.show'));

        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'provider'   => 'google',
            'provider_id' => 'google-id-42',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'social_link',
        ]);
    }

    public function test_the_login_button_never_links_even_for_a_linked_account(): void
    {
        $user = $this->makeGoogleUser(Role::CITOYEN);

        // A stale link intent left in the session must not survive the login
        // button: that is the state the old code misread.
        $this->actingAs($user);
        $this->withSession(['google_oauth_intent' => 'link']);

        $this->fakeGoogle($user->provider_id, $user->email);

        $this->get(route('auth.google'))
            ->assertRedirect();

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('citizen.dashboard'));

        $this->assertDatabaseMissing('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'social_link',
        ]);
    }

    public function test_linking_an_account_of_someone_else_is_refused(): void
    {
        $user  = $this->makeGoogleUser(Role::CITOYEN, provider: null, providerId: null);
        $owner = $this->makeGoogleUser(Role::CITOYEN, email: 'owner.google@test.local');

        $this->actingAs($user);
        $this->fakeGoogle($owner->provider_id, $owner->email);

        $this->get(route('profile.google.link'))->assertRedirect();
        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('error');

        $this->assertNull($user->fresh()->provider);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    private function makeGoogleUser(
        string $roleSlug,
        ?string $email = null,
        ?string $provider = 'google',
        ?string $providerId = null,
    ): User {
        $role = Role::where('slug', $roleSlug)->firstOrFail();

        $user = User::create([
            'name'              => 'Google '.ucfirst($roleSlug),
            'email'             => $email ?: uniqid('google.', true).'@test.local',
            'password'          => bcrypt('password123'),
            'is_active'         => true,
            'email_verified_at' => now(),
            'provider'          => $provider,
            'provider_id'       => $providerId ?? uniqid('gid-', true),
        ]);

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

        // Used by the two "go to Google" entry points.
        $provider->shouldReceive('with')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(
            redirect()->away('https://accounts.google.com/o/oauth2/v2/auth'),
        );

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
