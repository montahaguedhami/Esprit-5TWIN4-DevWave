<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
    }

    public function test_admin_can_login_and_reach_admin_dashboard(): void
    {
        $this->post(route('login.post'), [
            'email' => 'admin@aquasecure.tn',
            'password' => 'password123',
        ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('user.role', Role::ADMINISTRATEUR);

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_citizen_can_login_and_reach_citizen_dashboard(): void
    {
        $this->post(route('login.post'), [
            'email' => 'citoyen@aquasecure.tn',
            'password' => 'password123',
        ])
            ->assertRedirect(route('citizen.dashboard'))
            ->assertSessionHas('user.role', Role::CITOYEN);

        $this->get(route('citizen.dashboard'))
            ->assertOk()
            ->assertSessionHas('user.role', Role::CITOYEN);
    }

    public function test_technician_can_login_and_reach_technician_dashboard(): void
    {
        $this->post(route('login.post'), [
            'email' => 'amira@aquasecure.tn',
            'password' => 'password123',
        ])
            ->assertRedirect(route('technician.dashboard'))
            ->assertSessionHas('user.role', Role::TECHNICIEN);

        $this->get(route('technician.dashboard'))->assertOk();
    }

    public function test_manager_can_login_and_reach_manager_dashboard(): void
    {
        $this->post(route('login.post'), [
            'email' => 'gestionnaire@aquasecure.tn',
            'password' => 'password123',
        ])
            ->assertRedirect(route('manager.dashboard'))
            ->assertSessionHas('user.role', Role::MANAGER);

        $this->get(route('manager.dashboard'))->assertOk();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->from(route('auth.login'))
            ->post(route('login.post'), [
                'email' => 'admin@aquasecure.tn',
                'password' => 'mauvais-mot-de-passe',
            ])
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_registered_citizen_can_login_with_their_password(): void
    {
        $password = 'Nour@2026Secure!';

        $this->post(route('register.post'), [
            'name' => 'Nour Ben Salah',
            'email' => 'nour@example.tn',
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => '1',
        ])->assertRedirect(route('auth.login'));

        $user = User::where('email', 'nour@example.tn')->firstOrFail();
        $this->assertTrue($user->hasRole(Role::CITOYEN));

        $this->post(route('login.post'), [
            'email' => 'nour@example.tn',
            'password' => $password,
        ])
            ->assertRedirect(route('citizen.dashboard'))
            ->assertSessionHas('user.role', Role::CITOYEN);
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->from(route('auth.login'))
            ->post(route('login.post'), [
                'email' => 'inconnu@aquasecure.tn',
                'password' => 'password123',
            ])
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::where('email', 'citoyen@aquasecure.tn')->firstOrFail();
        $user->update(['is_active' => false]);

        $this->from(route('auth.login'))
            ->post(route('login.post'), [
                'email' => $user->email,
                'password' => 'password123',
            ])
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');
    }

    public function test_each_role_is_confined_to_its_own_interface(): void
    {
        $this->post(route('login.post'), ['email' => 'citoyen@aquasecure.tn', 'password' => 'password123']);
        $this->get(route('manager.dashboard'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('technician.dashboard'))->assertForbidden();
        $this->get(route('citizen.dashboard'))->assertOk();
        $this->assertSame(Role::CITOYEN, session('user.role'));

        $this->post(route('logout'));
        $this->post(route('login.post'), ['email' => 'amira@aquasecure.tn', 'password' => 'password123']);
        $this->get(route('citizen.dashboard'))->assertForbidden();
        $this->get(route('manager.dashboard'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('technician.dashboard'))->assertOk();
        $this->assertSame(Role::TECHNICIEN, session('user.role'));

        $this->post(route('logout'));
        $this->post(route('login.post'), ['email' => 'gestionnaire@aquasecure.tn', 'password' => 'password123']);
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('citizen.dashboard'))->assertForbidden();
        $this->get(route('technician.dashboard'))->assertForbidden();
        $this->get(route('manager.dashboard'))->assertOk();
        $this->assertSame(Role::MANAGER, session('user.role'));

        $this->post(route('logout'));
        $this->post(route('login.post'), ['email' => 'admin@aquasecure.tn', 'password' => 'password123']);
        $this->get(route('manager.dashboard'))->assertForbidden();
        $this->get(route('citizen.dashboard'))->assertForbidden();
        $this->get(route('technician.dashboard'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(Role::ADMINISTRATEUR, session('user.role'));
    }
}
