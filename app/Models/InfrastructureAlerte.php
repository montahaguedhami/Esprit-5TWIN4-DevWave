<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfrastructureAlerte extends Model
{
    protected $table = 'infrastructure_alertes';

    protected $fillable = [
        'infrastructure_id',
        'type',
        'message',
        'resolue',
    ];

    protected function casts(): array
    {
        return [
            'resolue' => 'boolean',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
