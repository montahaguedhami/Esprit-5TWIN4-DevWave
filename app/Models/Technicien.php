<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technicien extends Model
{
    /** @use HasFactory<\Database\Factories\TechnicienFactory> */
    use HasFactory;

    public const SPECIALITES = ['Canalisations', 'Pompage', 'Traitement de l\'eau', 'Électricité', 'Compteurs'];

    public const DISPONIBILITES = ['Disponible', 'En intervention', 'En congé'];

    protected $attributes = [
        'disponibilite' => 'Disponible',
    ];

    protected $fillable = [
        'nom',
        'specialite',
        'telephone',
        'disponibilite',
    ];

    /**
     * Un technicien réalise plusieurs interventions.
     */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
