<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervention extends Model
{
    /** @use HasFactory<\Database\Factories\InterventionFactory> */
    use HasFactory;

    public const STATUTS = ['Planifiée', 'En cours', 'Terminée', 'Annulée'];

    protected $fillable = [
        'technicien_id',
        'date',
        'description',
        'statut',
        'cout',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'cout' => 'decimal:2',
        ];
    }

    /**
     * Une intervention est réalisée par un seul technicien.
     */
    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Technicien::class);
    }
}
