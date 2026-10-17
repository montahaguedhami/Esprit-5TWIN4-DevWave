<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->policy = new UserPolicy();
    }

    public function test_admin_can_view_create_and_update_users(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $citizen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'c@test.com']);

        $this->assertTrue($this->policy->viewAny($admin)->allowed());
        $this->assertTrue($this->policy->view($admin, $citizen)->allowed());
        $this->assertTrue($this->policy->create($admin)->allowed());
        $this->assertTrue($this->policy->update($admin, $citizen)->allowed());
    }

    public function test_user_can_view_and_update_own_profile_without_users_permission(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $this->assertTrue($this->policy->view($citizen, $citizen)->allowed());
        $this->assertTrue($this->policy->update($citizen, $citizen)->allowed());
        $this->assertTrue($this->policy->viewAny($citizen)->denied());
        $this->assertTrue($this->policy->create($citizen)->denied());
    }

    public function test_user_cannot_delete_own_account(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a1@test.com']);
        $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a2@test.com']);

        $this->assertTrue($this->policy->delete($admin, $admin)->denied());
    }

    public function test_admin_cannot_delete_the_last_administrator(): void
    {
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->firstOrFail();
        $admins = User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get();
        $lastAdmin = $admins->first();
        foreach ($admins->skip(1) as $extra) {
            $extra->roles()->detach($adminRole->id);
        }
        $operator = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'operator@test.com']);

        $this->assertFalse($lastAdmin->refresh()->isLastAdministrator());
        $operator->roles()->detach($adminRole->id);

        $this->assertTrue($lastAdmin->refresh()->isLastAdministrator());
        $this->assertTrue($this->policy->delete($operator->fresh()->load('roles'), $lastAdmin)->denied());
    }

    public function test_non_admin_cannot_assign_roles_or_escalate_self(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citizen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'c@test.com']);

        $this->assertTrue($this->policy->assignRoles($manager, $citizen, [Role::TECHNICIEN])->denied());
        $this->assertTrue($this->policy->assignRoles($manager, $manager, [Role::ADMINISTRATEUR])->denied());
    }

    public function test_admin_can_assign_roles_except_demoting_last_admin(): void
    {
        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->firstOrFail();
        foreach (User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get() as $existingAdmin) {
            $existingAdmin->roles()->detach($adminRole->id);
        }

        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a1@test.com']);
        $otherAdmin = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a2@test.com']);
        $citizen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'c@test.com']);

        $this->assertTrue($this->policy->assignRoles($admin, $citizen, [Role::TECHNICIEN])->allowed());
        $this->assertTrue($this->policy->assignRoles($admin, $otherAdmin, [Role::ADMINISTRATEUR])->allowed());

        $otherAdmin->roles()->detach($adminRole->id);
        $this->assertTrue($this->policy->assignRoles($admin->fresh()->load('roles'), $admin, [Role::CITOYEN])->denied());
    }

    public function test_cannot_toggle_own_status_or_deactivate_last_admin(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'a1@test.com']);
        $citizen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'c@test.com']);

        $this->assertTrue($this->policy->toggleStatus($admin, $admin)->denied());
        $this->assertTrue($this->policy->toggleStatus($admin, $citizen)->allowed());

        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->firstOrFail();
        $admins = User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->get();
        foreach ($admins as $candidate) {
            if ($candidate->id !== $admin->id) {
                $candidate->roles()->detach($adminRole->id);
            }
        }

        $operator = $this->createUserWithRole(Role::ADMINISTRATEUR, ['email' => 'op@test.com']);
        $operator->roles()->detach($adminRole->id);

        $this->assertTrue($this->policy->toggleStatus($operator->fresh()->load('roles'), $admin->fresh())->denied());
    }

    public function test_citizen_cannot_toggle_status(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $other = $this->createUserWithRole(Role::CITOYEN, ['email' => 'o@test.com']);

        $this->assertTrue($this->policy->toggleStatus($citizen, $other)->denied());
        $this->assertTrue($this->policy->delete($citizen, $other)->denied());
    }
}
