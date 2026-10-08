<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesureQualite extends Model
{
    use HasFactory;

    protected $table = 'mesures_qualites';

    protected $fillable = [
        'reference',
        'point_mesure_id',
        'date_mesure',
        'ph',
        'turbidite',
        'chlore_residuel',
        'plomb',
        'nitrates',
        'is_verified',
        'verifier',
        'overall_compliance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_mesure' => 'datetime',
            'ph' => 'decimal:2',
            'turbidite' => 'decimal:2',
            'chlore_residuel' => 'decimal:3',
            'plomb' => 'decimal:3',
            'nitrates' => 'decimal:2',
            'is_verified' => 'boolean',
            'overall_compliance' => 'decimal:2',
        ];
    }

    public function pointMesure(): BelongsTo
    {
        return $this->belongsTo(PointMesure::class);
    }
}
