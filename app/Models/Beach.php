<?php

namespace App\Models;

use App\Support\TuniBeachCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beach extends Model
{
    use HasFactory;

    protected $table = 'tunibeach_beaches';

    protected $fillable = [
        'governorate_id', 'name', 'slug', 'description', 'latitude', 'longitude',
        'coordinates_verified', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'coordinates_verified' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => TuniBeachCache::invalidate());
        static::deleted(fn () => TuniBeachCache::invalidate());
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(BeachMeasurement::class);
    }

    public function bathingStatuses(): HasMany
    {
        return $this->hasMany(BeachBathingStatus::class);
    }
}
