<?php

namespace App\Models;

use App\Support\TuniBeachCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeachMeasurement extends Model
{
    use HasFactory;

    protected $table = 'tunibeach_measurements';

    protected $fillable = [
        'beach_id', 'reference', 'sampled_at', 'parameters', 'source_name', 'source_url',
        'is_verified', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'sampled_at' => 'datetime',
            'parameters' => 'array',
            'is_verified' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => TuniBeachCache::invalidate());
        static::deleted(fn () => TuniBeachCache::invalidate());
    }

    public function beach(): BelongsTo
    {
        return $this->belongsTo(Beach::class);
    }
}
