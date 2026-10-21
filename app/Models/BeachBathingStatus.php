<?php

namespace App\Models;

use App\Support\TuniBeachCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeachBathingStatus extends Model
{
    use HasFactory;

    public const STATUSES = ['safe', 'restricted', 'prohibited'];

    protected $table = 'tunibeach_bathing_statuses';

    protected $fillable = [
        'beach_id', 'status', 'source_name', 'source_url', 'source_reference', 'issued_at',
        'valid_from', 'valid_until', 'verified_at', 'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'verified_at' => 'datetime',
            'is_verified' => 'boolean',
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
