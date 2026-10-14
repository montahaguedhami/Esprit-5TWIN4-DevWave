<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    use HasFactory;

    public const ADMINISTRATEUR = 'administrateur';
    public const MANAGER = 'manager';
    public const TECHNICIEN = 'technicien';
    public const CITOYEN = 'citoyen';

    public const OFFICIAL_ROLES = [
        self::ADMINISTRATEUR,
        self::MANAGER,
        self::TECHNICIEN,
        self::CITOYEN,
    ];

    public const LABELS = [
        self::ADMINISTRATEUR => 'Administrateur',
        self::MANAGER => 'Gestionnaire',
        self::TECHNICIEN => 'Technicien',
        self::CITOYEN => 'Citoyen',
    ];

    protected $fillable = [
        'slug',
        'name',
        'description',
        'color',
    ];

    /**
     * Map historical or english aliases to official role slugs.
     */
    public static function normalizeSlug(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        $role = strtolower(trim($role));

        return match ($role) {
            'admin', 'administrator', 'administrateur' => self::ADMINISTRATEUR,
            'manager', 'gestionnaire' => self::MANAGER,
            'technician', 'technicien' => self::TECHNICIEN,
            'citizen', 'citoyen' => self::CITOYEN,
            default => $role,
        };
    }

    /**
     * Compare the current session role against official slugs or aliases.
     */
    public static function sessionMatches(string ...$roles): bool
    {
        if (! session()->has('user')) {
            return false;
        }

        $current = self::normalizeSlug(session('user.role'));
        if ($current === null) {
            return false;
        }

        foreach ($roles as $role) {
            if ($current === self::normalizeSlug($role)) {
                return true;
            }
        }

        return false;
    }

    public static function label(?string $role): string
    {
        $slug = self::normalizeSlug($role);

        return self::LABELS[$slug] ?? 'Utilisateur';
    }

    public static function homeRoute(?string $role): string
    {
        return match (self::normalizeSlug($role)) {
            self::ADMINISTRATEUR => 'admin.dashboard',
            self::MANAGER => 'manager.dashboard',
            self::TECHNICIEN => 'technician.dashboard',
            self::CITOYEN => 'citizen.dashboard',
            default => 'landing',
        };
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withTimestamps();
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }

    public function givePermissionTo(Permission|string $permission): self
    {
        $perm = is_string($permission)
            ? Permission::firstOrCreate(['name' => $permission], ['label' => $permission, 'module' => 'system'])
            : $permission;

        $this->permissions()->syncWithoutDetaching([$perm->id]);
        $this->flushPermissionCache();

        return $this;
    }

    public function revokePermission(Permission|string $permission): self
    {
        $perm = is_string($permission) ? Permission::where('name', $permission)->first() : $permission;
        if ($perm) {
            $this->permissions()->detach($perm->id);
            $this->flushPermissionCache();
        }

        return $this;
    }

    public function syncPermissions(array $permissionIdsOrNames): self
    {
        $ids = [];
        foreach ($permissionIdsOrNames as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            } elseif (is_string($item)) {
                $p = Permission::where('name', $item)->first();
                if ($p) {
                    $ids[] = $p->id;
                }
            } elseif ($item instanceof Permission) {
                $ids[] = $item->id;
            }
        }

        $this->permissions()->sync($ids);
        $this->flushPermissionCache();

        return $this;
    }

    public function flushPermissionCache(): void
    {
        // Flush all user cached permissions
        foreach ($this->users as $user) {
            $user->flushPermissionCache();
        }
    }
}
