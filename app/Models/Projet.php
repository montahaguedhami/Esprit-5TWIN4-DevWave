<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nom',
        'description',
        'date_debut',
        'date_fin',
        'budget',
        'progression',
        'statut',
        'adresse',
        'latitude',
        'longitude',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'budget' => 'decimal:2',
            'progression' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the financements for the projet.
     */
    public function financements(): HasMany
    {
        return $this->hasMany(Financement::class);
    }

    /**
     * Get the zones associated with the projet.
     */
    public function zones(): BelongsToMany
    {
         return $this->belongsToMany(Zone::class, 'projet_zone')->withTimestamps();
    }

    /**
     * Citizen funding requests submitted against this projet.
     */
    public function demandesFinancement(): HasMany
    {
        return $this->hasMany(DemandeFinancement::class);
    }

    /**
     * Amount already approved by a manager but not yet paid.
     *
     * These funds are committed: without this reservation, several requests
     * could be accepted in parallel and jointly exceed the remaining budget.
     */
    public function getMontantReserveAttribute(): float
    {
        // Set by the recommendation engine, which already fetched this figure
        // in SQL for every project. Using it avoids one query per project.
        if ($this->montantReservePreCalcule !== null) {
            return $this->montantReservePreCalcule;
        }

        return (float) $this->demandesFinancement()
            ->where('statut', DemandeFinancement::ACCEPTEE)
            ->sum('montant');
    }

    /**
     * Budget still open to new citizen contributions.
     *
     * Remaining budget minus what managers already approved but which has not
     * been paid yet.
     */
    public function getBudgetDisponibleAttribute(): float
    {
        return (float) ($this->budget - $this->total_finance - $this->montant_reserve);
    }

    /**
     * Can this projet still accept a citizen funding request?
     */
    public function estEligibleAuFinancementCitoyen(): bool
    {
        return in_array($this->statut, DemandeFinancement::STATUTS_PROJET_ELIGIBLE, true)
            && $this->budget_disponible > 0;
    }

    /**
      * Get the total amount financed for this projet.
     */
    public function getTotalFinanceAttribute(): float
    {
        // Pre-fetched in SQL by the recommendation engine. Without it, a list
        // of N projects would cost N extra queries (N+1).
        if ($this->totalFinancePreCalcule !== null) {
            return $this->totalFinancePreCalcule;
        }

        return (float) $this->financements()->sum('montant');
    }

    /* ── Valeurs déjà agrégées en SQL (anti N+1, voir RecommandationService) ── */

    private ?float $totalFinancePreCalcule = null;
    private ?float $montantReservePreCalcule = null;

    /**
     * Supply the financed total already fetched in SQL.
     *
     * Purely additive: when this is never called, every accessor keeps
     * behaving exactly as before.
     */
    public function setTotalFinanceCalc(float $montant): self
    {
        $this->totalFinancePreCalcule = $montant;

        return $this;
    }

    /**
     * Supply the reserved amount already fetched in SQL.
     */
    public function setMontantReserveCalc(float $montant): self
    {
        $this->montantReservePreCalcule = $montant;

        return $this;
    }

    /**
     * Get the remaining budget for this projet.
     */
    public function getBudgetRestantAttribute(): float
    {
        return (float) ($this->budget - $this->total_finance);
    }

    /**
     * Get the percentage of budget financed.
     */
    public function getPourcentageFinanceAttribute(): float
    {
        if ($this->budget == 0) {
            return 0;
        }
        return ($this->total_finance / $this->budget) * 100;
    }

    /**
     * Check if projet has geolocation.
     */
    public function hasGeolocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /* ── Recommandation de projets (V1 à règles) ─────────────────────────── */

    /**
     * Favourite rows pointing at this projet.
     */
    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    /**
     * How many citizens saved this projet.
     *
     * Used as an aggregate signal only — never to expose who saved what.
     */
    public function getNombreFavorisAttribute(): int
    {
        return $this->relationLoaded('favoris')
            ? $this->favoris->count()
            : $this->favoris()->count();
    }

    /**
     * Has the given user saved this projet?
     *
     * Returns false for a null user so the check is safe on public pages.
     */
    public function estFavoriDe(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        if ($this->relationLoaded('favoris')) {
            return $this->favoris
                ->contains(fn (Favori $favori): bool => $favori->user_id === $user->id);
        }

        return $this->favoris()->where('user_id', $user->id)->exists();
    }
}
