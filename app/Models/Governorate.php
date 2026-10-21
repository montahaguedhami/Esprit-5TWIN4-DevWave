<?php

namespace App\Models;

use App\Support\TuniBeachCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    use HasFactory;

    protected $table = 'tunibeach_governorates';

    protected $fillable = [
        'name', 'slug', 'is_verified', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => TuniBeachCache::invalidate());
        static::deleted(fn () => TuniBeachCache::invalidate());
    }

    public function beaches(): HasMany
    {
        return $this->hasMany(Beach::class);
    }
}
