<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfrastructureRiskPrediction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'infrastructure_id',
        'probability',
        'level',
        'factors',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'factors' => 'array',
            'computed_at' => 'datetime',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
