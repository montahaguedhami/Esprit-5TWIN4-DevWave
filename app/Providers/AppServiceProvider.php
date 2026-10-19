<?php

namespace App\Providers;

use App\Models\Infrastructure;
use App\Models\MesureQualite;
use App\Models\User;
use App\Models\Zone;
use App\Observers\InfrastructureObserver;
use App\Observers\InterventionObserver;
use App\Observers\ZoneObserver;
use App\Policies\MesureQualitePolicy;
use App\Policies\UserPolicy;
use App\Services\Payments\SimulatedPaymentProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Payment simulation: a LOCAL provider only. The `PAYMENT_DRIVER` value
        // is deliberately whitelisted so no real gateway can ever be enabled.
        $this->app->singleton(SimulatedPaymentProvider::class, function () {
            $driver = (string) config('financement.driver', 'simulated');

            if ($driver !== 'simulated') {
                throw new RuntimeException(
                    'PAYMENT_DRIVER inconnu : "'.$driver.'". Ce projet académique n\'accepte que "simulated"; '
                    .'aucune passerelle de paiement externe n\'est intégrée.'
                );
            }

            // The scenario picker is a development/demo affordance only.
            $choix = (bool) config('financement.scenario_choix', false)
                && app()->environment(['local', 'testing']);

            return new SimulatedPaymentProvider($choix);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Register Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(MesureQualite::class, MesureQualitePolicy::class);

        // Super-admin bypass for all gates, unless specific policy returns strict deny
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->isAdministrator()) {
                // Let the policy execute its own protections for sensitive operations
                if (in_array($ability, ['delete', 'assignRoles', 'toggleStatus'], true)) {
                    return null;
                }

                return true;
            }

            return null;
        });

        // Dynamic gate resolution for granular permission strings (e.g. 'users.view', 'measurements.validate')
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

        Infrastructure::observe(InfrastructureObserver::class);
        Zone::observe(ZoneObserver::class);
        \App\Models\Intervention::observe(InterventionObserver::class);
    }
}
