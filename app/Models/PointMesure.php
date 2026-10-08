<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PointMesure extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'zone',
        'adresse',
        'latitude',
        'longitude',
        'type',
        'statut',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function mesuresQualites(): HasMany
    {
        return $this->hasMany(MesureQualite::class);
    }

    public function derniereMesure(): HasOne
    {
        return $this->hasOne(MesureQualite::class)->latestOfMany('date_mesure');
    }
}
