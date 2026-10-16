<?php

namespace App\Models;

use App\Enums\InfrastructureEtat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfrastructureHistorique extends Model
{
    public $timestamps = false;

    protected $table = 'infrastructure_historiques';

    protected $fillable = [
        'infrastructure_id',
        'ancien_etat',
        'nouvel_etat',
        'commentaire',
        'user_name',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'ancien_etat' => InfrastructureEtat::class,
            'nouvel_etat' => InfrastructureEtat::class,
            'created_at' => 'datetime',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
