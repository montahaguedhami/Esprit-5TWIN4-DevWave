<?php

namespace App\Models;

use App\Support\TuniBeachCache;
use Illuminate\Database\Eloquent\Model;

class BeachHistoricalAdvisoryPoint extends Model
{
    protected $table = 'tunibeach_historical_advisory_points';

    protected $fillable = ['reported_at', 'governorate_name', 'point_name', 'reported_status', 'source_name', 'source_url'];

    protected function casts(): array
    {
        return ['reported_at' => 'date'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => TuniBeachCache::invalidate());
        static::deleted(fn () => TuniBeachCache::invalidate());
    }
}
