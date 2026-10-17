<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class HasRolesTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_assign_role_accepts_alias_and_records_audit_log(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        AuditLog::query()->delete();

        $user->assignRole('technician');

        $this->assertTrue($user->fresh()->load('roles')->isTechnician());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'role_assigned',
            'resource_type' => 'User',
            'resource_id' => $user->id,
        ]);
    }

    public function test_has_role_accepts_array_and_aliases(): void
    {
        $user = $this->createUserWithRole(Role::MANAGER);

        $this->assertTrue($user->hasRole(['citizen', 'gestionnaire']));
        $this->assertTrue($user->hasAnyRole(['technicien', 'manager']));
        $this->assertFalse($user->hasRole(['citoyen', 'technicien']));
    }

    public function test_primary_role_follows_priority_order(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->assertSame(Role::CITOYEN, $user->primary_role);

        $user->assignRole(Role::CITOYEN);
        $this->assertSame(Role::CITOYEN, $user->fresh()->primary_role);

        $user->assignRole(Role::TECHNICIEN);
        $this->assertSame(Role::TECHNICIEN, $user->fresh()->primary_role);

        $user->assignRole(Role::MANAGER);
        $this->assertSame(Role::MANAGER, $user->fresh()->primary_role);

        $user->assignRole(Role::ADMINISTRATEUR);
        $this->assertSame(Role::ADMINISTRATEUR, $user->fresh()->primary_role);
    }

    public function test_inactive_user_has_no_permissions_even_as_admin(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR, ['is_active' => false]);

        $this->assertFalse($admin->hasPermission('users.view'));
        $this->assertFalse($admin->hasPermission('measurements.create'));
    }

    public function test_administrator_has_every_permission_without_explicit_check(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);

        $this->assertTrue($admin->hasPermission('users.delete'));
        $this->assertTrue($admin->hasPermission('unknown.permission'));
    }

    public function test_remove_role_detaches_and_records_audit(): void
    {
        $user = $this->createUserWithRole(Role::TECHNICIEN);
        $user->assignRole(Role::CITOYEN);
        AuditLog::query()->delete();

        $user->removeRole('technician');

        $this->assertFalse($user->fresh()->load('roles')->isTechnician());
        $this->assertTrue($user->fresh()->load('roles')->isCitizen());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'role_removed',
            'resource_id' => $user->id,
        ]);
    }

    public function test_sync_roles_replaces_existing_roles(): void
    {
        $user = $this->createUserWithRole(Role::CITOYEN);
        $user->syncRoles(['technicien']);

        $user->refresh()->load('roles');
        $this->assertTrue($user->isTechnician());
        $this->assertFalse($user->isCitizen());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'roles_synchronized',
            'resource_id' => $user->id,
        ]);
    }

    public function test_permission_cache_is_flushed_when_role_permissions_change(): void
    {
        $user = $this->createUserWithRole(Role::CITOYEN);
        $this->assertFalse($user->hasPermission('measurements.create'));

        $role = Role::where('slug', Role::CITOYEN)->firstOrFail();
        $role->givePermissionTo('measurements.create');

        $this->assertTrue($user->fresh()->hasPermission('measurements.create'));

        $role->revokePermission('measurements.create');
        $this->assertFalse($user->fresh()->hasPermission('measurements.create'));
    }

    public function test_is_last_administrator_is_false_when_another_active_admin_exists(): void
    {
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->firstOrFail();
        foreach (User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get() as $existingAdmin) {
            $existingAdmin->roles()->detach($adminRole->id);
        }

        $admin1 = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a1@test.com']);
        $admin2 = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a2@test.com']);

        $this->assertFalse($admin1->isLastAdministrator());
        $this->assertFalse($admin2->isLastAdministrator());

        $admin2->update(['is_active' => false]);
        $this->assertTrue($admin1->fresh()->isLastAdministrator());
    }

    public function test_non_admin_is_never_the_last_administrator(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $this->assertFalse($citizen->isLastAdministrator());
    }

    public function test_cannot_remove_admin_role_from_last_administrator(): void
    {
        $admin = $this->keepSingleAdministrator();

        $this->expectException(ValidationException::class);
        $admin->removeRole(Role::ADMINISTRATEUR);
    }

    public function test_cannot_sync_away_last_administrator_role(): void
    {
        $admin = $this->keepSingleAdministrator();

        $this->expectException(ValidationException::class);
        $admin->syncRoles([Role::CITOYEN]);
    }

    public function test_cannot_delete_last_administrator(): void
    {
        $admin = $this->keepSingleAdministrator();

        $this->expectException(ValidationException::class);
        $admin->delete();
    }

    public function test_assign_unknown_role_does_nothing(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('role-inexistant');

        $this->assertSame([], $user->fresh()->getRoleSlugs());
    }

    private function keepSingleAdministrator(): User
    {
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->firstOrFail();
        $admins = User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get();
        $lastAdmin = $admins->first();

        foreach ($admins->skip(1) as $extra) {
            $extra->roles()->detach($adminRole->id);
        }

        return $lastAdmin->refresh();
    }
}
