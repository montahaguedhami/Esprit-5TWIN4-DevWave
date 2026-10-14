<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\MesureQualite;
use App\Models\Permission;
use App\Models\PointMesure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function seedRbac(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
    }

    private function createUserWithRole(string $roleSlug, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
        ], $attrs));

        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->assignRole($role);

        return $user->refresh()->load('roles');
    }

    private function loginSession(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->primary_role,
                'role_key' => $user->primary_role,
                'roles' => $user->getRoleSlugs(),
            ],
        ];
    }

    // ============ ROLE MODEL TESTS ============

    public function test_four_official_roles_are_seeded(): void
    {
        $this->seedRbac();

        $this->assertDatabaseCount('roles', 4);
        foreach (Role::OFFICIAL_ROLES as $slug) {
            $this->assertDatabaseHas('roles', ['slug' => $slug]);
        }
    }

    public function test_role_slug_normalization(): void
    {
        $this->assertEquals('administrateur', Role::normalizeSlug('admin'));
        $this->assertEquals('administrateur', Role::normalizeSlug('administrator'));
        $this->assertEquals('administrateur', Role::normalizeSlug('administrateur'));
        $this->assertEquals('manager', Role::normalizeSlug('manager'));
        $this->assertEquals('manager', Role::normalizeSlug('gestionnaire'));
        $this->assertEquals('technicien', Role::normalizeSlug('technician'));
        $this->assertEquals('technicien', Role::normalizeSlug('technicien'));
        $this->assertEquals('citoyen', Role::normalizeSlug('citizen'));
        $this->assertEquals('citoyen', Role::normalizeSlug('citoyen'));
        $this->assertNull(Role::normalizeSlug(null));
    }

    public function test_permissions_are_seeded_for_each_role(): void
    {
        $this->seedRbac();

        $admin = Role::where('slug', Role::ADMINISTRATEUR)->first();
        $manager = Role::where('slug', Role::MANAGER)->first();
        $tech = Role::where('slug', Role::TECHNICIEN)->first();
        $citizen = Role::where('slug', Role::CITOYEN)->first();

        // Admin gets all permissions
        $totalPerms = Permission::count();
        $this->assertEquals($totalPerms, $admin->permissions()->count());

        // Manager has more permissions than tech
        $this->assertGreaterThan($tech->permissions()->count(), $manager->permissions()->count());

        // Both tech and citizen have limited permissions, both less than manager
        $this->assertGreaterThan($citizen->permissions()->count(), $manager->permissions()->count());
        $this->assertGreaterThan($tech->permissions()->count(), $manager->permissions()->count());

        // Citizen and tech both have profile.view and profile.update
        $this->assertTrue($citizen->permissions()->where('name', 'profile.view')->exists());
        $this->assertTrue($tech->permissions()->where('name', 'measurements.create')->exists());
        $this->assertFalse($citizen->permissions()->where('name', 'measurements.create')->exists());
    }

    // ============ USER - ROLE ASSIGNMENT TESTS ============

    public function test_user_can_be_assigned_a_role(): void
    {
        $this->seedRbac();
        $user = User::factory()->create(['is_active' => true]);

        $user->assignRole('citoyen');

        $this->assertTrue($user->hasRole('citoyen'));
        $this->assertTrue($user->isCitizen());
        $this->assertFalse($user->isAdministrator());
    }

    public function test_user_hasRole_accepts_english_and_french_slugs(): void
    {
        $this->seedRbac();
        $user = $this->createUserWithRole(Role::TECHNICIEN);

        $this->assertTrue($user->hasRole('technicien'));
        $this->assertTrue($user->hasRole('technician'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('citoyen'));
    }

    public function test_user_primary_role_returns_highest_priority(): void
    {
        $this->seedRbac();
        $user = User::factory()->create(['is_active' => true]);

        $user->assignRole('citoyen');
        $this->assertEquals(Role::CITOYEN, $user->refresh()->primary_role);

        $user->assignRole('administrateur');
        $this->assertEquals(Role::ADMINISTRATEUR, $user->refresh()->primary_role);
    }

    // ============ PERMISSION CHECKS ============

    public function test_administrator_has_all_permissions(): void
    {
        $this->seedRbac();
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);

        $this->assertTrue($admin->hasPermission('users.view'));
        $this->assertTrue($admin->hasPermission('users.delete'));
        $this->assertTrue($admin->hasPermission('users.assign_roles'));
        $this->assertTrue($admin->hasPermission('measurements.validate'));
        $this->assertTrue($admin->hasPermission('audit.view'));
        $this->assertTrue($admin->hasPermission('roles.manage'));
    }

    public function test_technician_can_create_measurements_but_not_validate(): void
    {
        $this->seedRbac();
        $tech = $this->createUserWithRole(Role::TECHNICIEN);

        $this->assertTrue($tech->hasPermission('measurements.create'));
        $this->assertTrue($tech->hasPermission('measurements.view'));
        $this->assertTrue($tech->hasPermission('measurements.update'));
        $this->assertFalse($tech->hasPermission('measurements.validate'));
        $this->assertFalse($tech->hasPermission('measurements.delete'));
    }

    public function test_citizen_cannot_create_measurements(): void
    {
        $this->seedRbac();
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $this->assertTrue($citizen->hasPermission('measurements.view'));
        $this->assertFalse($citizen->hasPermission('measurements.create'));
        $this->assertFalse($citizen->hasPermission('measurements.update'));
        $this->assertFalse($citizen->hasPermission('measurements.validate'));
    }

    public function test_citizen_cannot_manage_users_or_roles(): void
    {
        $this->seedRbac();
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $this->assertFalse($citizen->hasPermission('users.view'));
        $this->assertFalse($citizen->hasPermission('users.create'));
        $this->assertFalse($citizen->hasPermission('users.assign_roles'));
        $this->assertFalse($citizen->hasPermission('roles.view'));
        $this->assertFalse($citizen->hasPermission('roles.manage'));
    }

    public function test_technician_cannot_manage_users_or_roles(): void
    {
        $this->seedRbac();
        $tech = $this->createUserWithRole(Role::TECHNICIEN);

        $this->assertFalse($tech->hasPermission('users.view'));
        $this->assertFalse($tech->hasPermission('users.create'));
        $this->assertFalse($tech->hasPermission('users.assign_roles'));
        $this->assertFalse($tech->hasPermission('roles.manage'));
        $this->assertFalse($tech->hasPermission('audit.view'));
    }

    public function test_inactive_user_has_no_permissions(): void
    {
        $this->seedRbac();
        $user = $this->createUserWithRole(Role::ADMINISTRATEUR, ['is_active' => false]);

        $this->assertFalse($user->hasPermission('users.view'));
        $this->assertFalse($user->hasPermission('measurements.create'));
    }

    // ============ LAST ADMINISTRATOR PROTECTION ============

    public function test_cannot_delete_last_administrator(): void
    {
        $this->seedRbac();

        // Remove all other admins so only one remains
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->first();
        $admins = User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get();
        // Keep only the first active admin
        $lastAdmin = $admins->first();
        foreach ($admins->skip(1) as $extra) {
            $extra->roles()->detach($adminRole->id);
        }

        $this->assertTrue($lastAdmin->refresh()->isLastAdministrator());

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $lastAdmin->delete();
    }

    public function test_cannot_remove_admin_role_from_last_administrator(): void
    {
        $this->seedRbac();

        // Remove all other admins so only one remains
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->first();
        $admins = User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get();
        $lastAdmin = $admins->first();
        foreach ($admins->skip(1) as $extra) {
            $extra->roles()->detach($adminRole->id);
        }

        $this->assertTrue($lastAdmin->refresh()->isLastAdministrator());

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $lastAdmin->removeRole('administrateur');
    }

    public function test_cannot_demote_last_admin_via_syncRoles(): void
    {
        $this->seedRbac();

        // Remove all other admins so only one remains
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->first();
        $admins = User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get();
        $lastAdmin = $admins->first();
        foreach ($admins->skip(1) as $extra) {
            $extra->roles()->detach($adminRole->id);
        }

        $this->assertTrue($lastAdmin->refresh()->isLastAdministrator());

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $lastAdmin->syncRoles(['citoyen']);
    }

    public function test_can_delete_admin_when_another_exists(): void
    {
        $this->seedRbac();
        $admin1 = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $admin2 = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'admin2@test.com']);

        $this->assertFalse($admin1->isLastAdministrator());
        $admin1->delete();
        $this->assertDatabaseMissing('users', ['id' => $admin1->id]);
    }

    // ============ SELF-ROLE ATTRIBUTION PREVENTION ============

    public function test_user_policy_prevents_self_role_escalation_for_non_admin(): void
    {
        $this->seedRbac();
        $manager = $this->createUserWithRole(Role::MANAGER);

        $policy = new \App\Policies\UserPolicy();
        $response = $policy->assignRoles($manager, $manager, ['administrateur']);
        $this->assertTrue($response->denied());
    }

    // ============ ADMIN USERS CRUD TESTS (Web Routes) ============

    public function test_admin_can_list_users(): void
    {
        $this->seedRbac();
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);

        $response = $this->actingAs($admin)
            ->withSession($this->loginSession($admin))
            ->get(route('admin.users.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_user(): void
    {
        $this->seedRbac();
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);

        $response = $this->actingAs($admin)
            ->withSession($this->loginSession($admin))
            ->post(route('admin.users.store'), [
                'name' => 'New Technicien',
                'email' => 'new-tech@test.com',
                'password' => 'password123',
                'role' => 'technicien',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'new-tech@test.com']);

        $newUser = User::where('email', 'new-tech@test.com')->first();
        $this->assertTrue($newUser->hasRole(Role::TECHNICIEN));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $this->seedRbac();
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $citizen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'toggle@test.com']);

        $this->assertTrue($citizen->is_active);

        $response = $this->actingAs($admin)
            ->withSession($this->loginSession($admin))
            ->patch(route('admin.users.toggle-status', $citizen->id));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($citizen->refresh()->is_active);
    }

    public function test_admin_cannot_toggle_last_admin_status(): void
    {
        $this->seedRbac();
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);

        $response = $this->actingAs($admin)
            ->withSession($this->loginSession($admin))
            ->patch(route('admin.users.toggle-status', $admin->id));

        // Should fail because user cannot deactivate themselves
        $response->assertStatus(403);
    }

    public function test_admin_can_delete_non_admin_user(): void
    {
        $this->seedRbac();
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $citizen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'del@test.com']);

        $response = $this->actingAs($admin)
            ->withSession($this->loginSession($admin))
            ->delete(route('admin.users.destroy', $citizen->id));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $citizen->id]);
    }

    // ============ ROLE ROUTE PROTECTION ============

    public function test_citizen_cannot_access_admin_users(): void
    {
        $this->seedRbac();
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $response = $this->actingAs($citizen)
            ->withSession($this->loginSession($citizen))
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_technician_cannot_access_admin_users(): void
    {
        $this->seedRbac();
        $tech = $this->createUserWithRole(Role::TECHNICIEN);

        $response = $this->actingAs($tech)
            ->withSession($this->loginSession($tech))
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_create_admin_user(): void
    {
        $this->seedRbac();
        $manager = $this->createUserWithRole(Role::MANAGER);

        $response = $this->actingAs($manager)
            ->withSession($this->loginSession($manager))
            ->post(route('admin.users.store'), [
                'name' => 'Hacker Admin',
                'email' => 'hacker@test.com',
                'password' => 'password123',
                'role' => 'administrateur',
            ]);

        // Manager does not have users.create permission, should be denied
        $response->assertStatus(403);
    }

    // ============ AUDIT LOG TESTS ============

    public function test_role_assignment_creates_audit_log(): void
    {
        $this->seedRbac();
        $user = User::factory()->create(['is_active' => true]);

        AuditLog::query()->delete();
        $user->assignRole('technicien');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'role_assigned',
            'resource_type' => 'User',
            'resource_id' => $user->id,
        ]);
    }

    public function test_login_creates_audit_log(): void
    {
        $this->seedRbac();
        AuditLog::query()->delete();

        $response = $this->post(route('login.post'), [
            'email' => 'admin@aquasecure.tn',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_login',
        ]);
    }

    // ============ MEASUREMENT PERMISSION TESTS (API) ============

    public function test_api_citizen_cannot_create_measurement(): void
    {
        $this->seedRbac();
        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $point = PointMesure::create([
            'nom' => 'Point Test',
            'code' => 'PT-001',
            'zone' => 'Zone Test',
            'type' => 'forage',
            'statut' => 'actif',
        ]);

        $response = $this->actingAs($citizen)
            ->withSession($this->loginSession($citizen))
            ->postJson('/api/mesures', [
                'reference' => 'MES-TEST-001',
                'point_mesure_id' => $point->id,
                'date_mesure' => now()->toDateTimeString(),
                'ph' => 7.2,
                'turbidite' => 0.5,
                'chlore_residuel' => 0.3,
                'plomb' => 0.005,
                'nitrates' => 10.0,
            ]);

        $response->assertStatus(403);
    }

    // ============ RBAC SEEDER IDEMPOTENCY ============

    public function test_rbac_seeder_can_run_twice_without_error(): void
    {
        $this->seedRbac();
        $roleCount1 = Role::count();
        $permCount1 = Permission::count();

        $this->seedRbac();
        $roleCount2 = Role::count();
        $permCount2 = Permission::count();

        $this->assertEquals($roleCount1, $roleCount2);
        $this->assertEquals($permCount1, $permCount2);
    }

    // ============ PERMISSION CACHE INVALIDATION ============

    public function test_permission_cache_is_invalidated_on_role_change(): void
    {
        $this->seedRbac();
        $user = $this->createUserWithRole(Role::CITOYEN);

        // Citizen cannot validate measurements
        $this->assertFalse($user->hasPermission('measurements.validate'));

        // Assign manager role
        $user->assignRole('manager');
        $user->refresh()->load('roles');

        // Now should have validate permission (cache flushed)
        $this->assertTrue($user->hasPermission('measurements.validate'));
    }
}
