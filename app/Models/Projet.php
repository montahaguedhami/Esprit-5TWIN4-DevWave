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
        return (float) $this->financements()->sum('montant');
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
}
