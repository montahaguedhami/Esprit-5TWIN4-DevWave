<?php

namespace App\Models;

use App\Enums\ZoneStatut;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    /** @use HasFactory<\Database\Factories\ZoneFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'description',
        'localisation',
        'latitude',
        'longitude',
        'superficie_km2',
        'population_desservie',
        'statut',
        'responsable_id',
    ];

    protected function casts(): array
    {
        return [
            'statut' => ZoneStatut::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'superficie_km2' => 'decimal:2',
            'population_desservie' => 'integer',
        ];
    }

    public function infrastructures(): HasMany
    {
        return $this->hasMany(Infrastructure::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->latest('date_signalement');
    }

    public function pointsMesure(): HasMany
    {
        return $this->hasMany(PointMesure::class);
    }

    public function projets(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'projet_zone')->withTimestamps();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('nom', 'like', "%{$term}%")
                ->orWhere('localisation', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
