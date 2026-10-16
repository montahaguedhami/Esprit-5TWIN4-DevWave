<?php

namespace App\Observers;

use App\Models\Zone;
use Illuminate\Support\Facades\Cache;

class ZoneObserver
{
    public function created(Zone $zone): void
    {
        Cache::forget('zone_stats_globales');
    }

    public function updated(Zone $zone): void
    {
        Cache::forget('zone_stats_globales');
    }

    public function deleted(Zone $zone): void
    {
        Cache::forget('zone_stats_globales');
    }

    public function restored(Zone $zone): void
    {
        Cache::forget('zone_stats_globales');
    }
}
