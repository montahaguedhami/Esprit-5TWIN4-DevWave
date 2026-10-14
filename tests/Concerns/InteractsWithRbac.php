<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;

trait InteractsWithRbac
{
    protected function seedRbac(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
    }

    protected function createUserWithRole(string $roleSlug, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
        ], $attrs));

        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->assignRole($role);

        return $user->refresh()->load('roles');
    }
}
