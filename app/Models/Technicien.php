<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'user_id',
        'nom',
        'specialite',
        'telephone',
        'disponibilite',
    ];

    /**
     * Compte utilisateur (rôle technicien) qui se connecte avec cette fiche.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un technicien réalise plusieurs interventions.
     */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function actionsCorrectives(): HasMany
    {
        return $this->hasMany(ActionCorrective::class);
    }
}