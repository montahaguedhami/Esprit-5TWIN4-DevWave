<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
