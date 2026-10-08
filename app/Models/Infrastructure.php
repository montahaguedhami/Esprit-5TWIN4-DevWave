<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Infrastructure extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'localisation',
        'etat',
        'date_installation',
        'zone_id',
    ];

    protected function casts(): array
    {
        return [
            'date_installation' => 'date',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
