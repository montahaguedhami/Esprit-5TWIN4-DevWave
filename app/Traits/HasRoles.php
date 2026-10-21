<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Assign a role to user (checks for valid official roles).
     */
    public function assignRole(Role|string $role): self
    {
        $roleModel = $this->resolveRoleModel($role);

        if ($roleModel) {
            $this->roles()->syncWithoutDetaching([$roleModel->id]);
            $this->flushPermissionCache();

            try {
                AuditLog::record('role_assigned', 'User', $this->id, [
                    'role' => $roleModel->slug,
                    'user_email' => $this->email,
                ]);
            } catch (\Throwable $e) {
                // Audit logging must not make a successful role assignment
                // look like a failed registration.
                report($e);
            }
        }

        return $this;
    }

    /**
     * Remove a role from user, protecting the last administrator.
     */
    public function removeRole(Role|string $role): self
    {
        $roleModel = $this->resolveRoleModel($role);

        if ($roleModel) {
            if ($roleModel->slug === Role::ADMINISTRATEUR && $this->isLastAdministrator()) {
                throw ValidationException::withMessages([
                    'role' => ['Action impossible : vous ne pouvez pas retirer le rôle administrateur au dernier administrateur actif du système.'],
                ]);
            }

            $this->roles()->detach($roleModel->id);
            $this->flushPermissionCache();

            AuditLog::record('role_removed', 'User', $this->id, [
                'role' => $roleModel->slug,
                'user_email' => $this->email,
            ]);
        }

        return $this;
    }

    /**
     * Synchronize user roles.
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = [];
        $hasAdminBefore = $this->hasRole(Role::ADMINISTRATEUR);

        foreach ($roles as $role) {
            $model = $this->resolveRoleModel($role);
            if ($model) {
                $roleIds[] = $model->id;
            }
        }

        $adminRole = Role::where('slug', Role::ADMINISTRATEUR)->first();
        $willHaveAdmin = $adminRole && in_array($adminRole->id, $roleIds, true);

        if ($hasAdminBefore && ! $willHaveAdmin && $this->isLastAdministrator()) {
            throw ValidationException::withMessages([
                'roles' => ['Protection administrateur : impossible de rétrograder le dernier administrateur actif du système.'],
            ]);
        }

        $this->roles()->sync($roleIds);
        $this->flushPermissionCache();

        AuditLog::record('roles_synchronized', 'User', $this->id, [
            'roles' => $this->roles()->pluck('slug')->toArray(),
            'user_email' => $this->email,
        ]);

        return $this;
    }

    /**
     * Check if user has specific role or roles.
     */
    public function hasRole(string|array $roles): bool
    {
        $rolesList = is_array($roles) ? $roles : [$roles];
        $normalizedSearch = array_map(fn ($r) => Role::normalizeSlug($r), $rolesList);

        $userRoles = $this->getRoleSlugs();

        foreach ($normalizedSearch as $role) {
            if (in_array($role, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Get array of all role slugs assigned to this user.
     *
     * @return array<string>
     */
    public function getRoleSlugs(): array
    {
        return $this->roles->pluck('slug')->toArray();
    }

    /**
     * Get primary role slug for display / routing.
     */
    public function getPrimaryRoleAttribute(): string
    {
        $roles = $this->getRoleSlugs();

        if (in_array(Role::ADMINISTRATEUR, $roles, true)) {
            return Role::ADMINISTRATEUR;
        }
        if (in_array(Role::MANAGER, $roles, true)) {
            return Role::MANAGER;
        }
        if (in_array(Role::TECHNICIEN, $roles, true)) {
            return Role::TECHNICIEN;
        }
        if (in_array(Role::CITOYEN, $roles, true)) {
            return Role::CITOYEN;
        }

        return Role::CITOYEN;
    }

    /**
     * Check if user has a specific granular permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // Administrator has full permissions
        if ($this->hasRole(Role::ADMINISTRATEUR)) {
            return true;
        }

        return $this->getAllPermissions()->contains('name', $permissionName);
    }

    /**
     * Get all permissions from all user roles with cache invalidation support.
     */
    public function getAllPermissions(): Collection
    {
        $cacheKey = "user_permissions_{$this->id}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions)
                ->unique('id');
        });
    }

    /**
     * Flush cached permissions immediately.
     */
    public function flushPermissionCache(): void
    {
        try {
            Cache::forget("user_permissions_{$this->id}");
        } catch (\Throwable $e) {
            // Cached permissions are derived data; cache outages should not
            // block account creation or role changes.
            report($e);
        }
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(Role::ADMINISTRATEUR);
    }

    public function isManager(): bool
    {
        return $this->hasRole(Role::MANAGER);
    }

    public function isTechnician(): bool
    {
        return $this->hasRole(Role::TECHNICIEN);
    }

    public function isCitizen(): bool
    {
        return $this->hasRole(Role::CITOYEN);
    }

    /**
     * Protect against deleting or demoting the last active administrator.
     */
    public function isLastAdministrator(): bool
    {
        if (! $this->isAdministrator()) {
            return false;
        }

        $activeAdminCount = self::query()
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->where('slug', Role::ADMINISTRATEUR);
            })
            ->count();

        return $activeAdminCount <= 1;
    }

    private function resolveRoleModel(Role|string $role): ?Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        $slug = Role::normalizeSlug($role);

        return Role::where('slug', $slug)->first();
    }
}
