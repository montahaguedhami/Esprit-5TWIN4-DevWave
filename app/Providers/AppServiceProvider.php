<?php

namespace App\Providers;

use App\Models\Infrastructure;
use App\Models\Zone;
use App\Observers\InfrastructureObserver;
use App\Observers\IncidentObserver;
use App\Observers\InterventionObserver;
use App\Observers\ZoneObserver;
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

        Infrastructure::observe(InfrastructureObserver::class);
        \App\Models\Incident::observe(IncidentObserver::class);
        Zone::observe(ZoneObserver::class);
        \App\Models\Intervention::observe(InterventionObserver::class);
    }
}
