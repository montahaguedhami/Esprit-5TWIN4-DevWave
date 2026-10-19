<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervention extends Model
{
    /** @use HasFactory<\Database\Factories\InterventionFactory> */
    use HasFactory;

    public ?int $ancienneInfrastructureId = null;

    public const STATUTS = ['Planifiée', 'En cours', 'Terminée', 'Annulée'];

    public const TYPE_CORRECTIVE = 'Corrective';

    public const TYPE_PREVENTIVE = 'Préventive';

    public const TYPES = [self::TYPE_CORRECTIVE, self::TYPE_PREVENTIVE];

    protected $attributes = [
        'statut' => 'Planifiée',
        'type' => self::TYPE_CORRECTIVE,
    ];

    protected $fillable = [
        'technicien_id',
        'infrastructure_id',
        'date',
        'description',
        'statut',
        'type',
        'cout',
    ];

    public function estPreventive(): bool
    {
        return $this->type === self::TYPE_PREVENTIVE;
    }

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

    /**
     * Une intervention peut être rattachée à une infrastructure du réseau.
     */
    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
