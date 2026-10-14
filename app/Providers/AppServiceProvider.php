<?php

namespace App\Providers;

<<<<<<< HEAD
=======
use App\Models\MesureQualite;
use App\Models\User;
use App\Policies\MesureQualitePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
>>>>>>> user
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
<<<<<<< HEAD
=======

        // Register Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(MesureQualite::class, MesureQualitePolicy::class);

        // Super-admin bypass for all gates, unless specific policy returns strict deny
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->isAdministrator()) {
                // If it's a delete or role assignment on User model, let the policy execute its protections
                if (in_array($ability, ['delete', 'assignRoles', 'toggleStatus'], true)) {
                    return null;
                }

                return true;
            }

            return null;
        });

        // Dynamic gate resolution for any granular permission string (e.g. 'users.view', 'measurements.validate')
        Gate::after(function ($user, $ability, $result) {
            if ($result !== null) {
                return $result;
            }

            if ($user instanceof User && is_string($ability) && str_contains($ability, '.')) {
                return $user->hasPermission($ability);
            }

            return null;
        });

        // Blade Directives for RBAC
        Blade::if('role', function ($role) {
            $user = auth()->user();
            if ($user instanceof User) {
                return $user->hasRole($role);
            }
            $sessionRole = session('user.role');

            return \App\Models\Role::normalizeSlug($sessionRole) === \App\Models\Role::normalizeSlug($role);
        });

        Blade::if('permission', function ($permission) {
            $user = auth()->user();
            if ($user instanceof User) {
                return $user->hasPermission($permission);
            }

            return false;
        });
>>>>>>> user
    }
}
