<?php

namespace App\Models;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Services\InfrastructureHealthService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Infrastructure extends Model
{
    /** @use HasFactory<\Database\Factories\InfrastructureFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Commentaire temporaire utilisé lors d'un changement d'état (transmis à l'observer).
     */
    public ?string $etat_changement_commentaire = null;

    /**
     * Conservation temporaire en mémoire de l'ancien état avant sauvegarde.
     */
    public mixed $ancienEtatAvantSauvegarde = null;

    protected $fillable = [
        'nom',
        'type',
        'localisation',
        'etat',
        'date_installation',
        'zone_id',
        'latitude',
        'longitude',
        'capacite',
        'unite_capacite',
        'fabricant',
        'reference',
        'date_derniere_maintenance',
        'date_prochaine_maintenance',
        'duree_vie_annees',
        'niveau_criticite',
    ];

    protected function casts(): array
    {
        return [
            'type' => InfrastructureType::class,
            'etat' => InfrastructureEtat::class,
            'niveau_criticite' => NiveauCriticite::class,
            'date_installation' => 'date',
            'date_derniere_maintenance' => 'date',
            'date_prochaine_maintenance' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'capacite' => 'decimal:2',
            'duree_vie_annees' => 'integer',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function pointsMesure(): HasMany
    {
        return $this->hasMany(PointMesure::class);
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(InfrastructureHistorique::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(InfrastructureAlerte::class)->orderByDesc('created_at');
    }

    public function alertesActives(): HasMany
    {
        return $this->hasMany(InfrastructureAlerte::class)->where('resolue', false)->orderByDesc('created_at');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class)->orderByDesc('date');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->latest('date_signalement');
    }

    /**
     * Calcule le score de santé de l'infrastructure via le service dédié.
     *
     * @return array{score: int, niveau: string, facteurs: list<string>}
     */
    public function getSanteAttribute(): array
    {
        return app(InfrastructureHealthService::class)->score($this);
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
                ->orWhere('type', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhere('fabricant', 'like', "%{$term}%");
        });
    }

    public function scopeDeType(Builder $query, InfrastructureType|string|null $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        $valeur = $type instanceof InfrastructureType ? $type->value : $type;

        return $query->where('type', $valeur);
    }

    public function scopeDEtat(Builder $query, InfrastructureEtat|string|null $etat): Builder
    {
        if (empty($etat)) {
            return $query;
        }

        $valeur = $etat instanceof InfrastructureEtat ? $etat->value : $etat;

        return $query->where('etat', $valeur);
    }

    public function scopeDeEtat(Builder $query, InfrastructureEtat|string|null $etat): Builder
    {
        return $this->scopeDEtat($query, $etat);
    }

    public function scopeDeZone(Builder $query, ?int $zoneId): Builder
    {
        if (empty($zoneId)) {
            return $query;
        }

        return $query->where('zone_id', $zoneId);
    }

    public function scopeEnRetardMaintenance(Builder $query): Builder
    {
        return $query->whereNotNull('date_prochaine_maintenance')
            ->whereDate('date_prochaine_maintenance', '<', now()->toDateString());
    }

    public function scopeSanteCritique(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where('etat', InfrastructureEtat::HorsService->value)
                ->orWhere(function (Builder $sub): void {
                    $sub->whereNotNull('date_prochaine_maintenance')
                        ->whereDate('date_prochaine_maintenance', '<', now()->subDays(30)->toDateString());
                });
        });
    }
}
