<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'localisation',
    ];

    public function infrastructures(): HasMany
    {
        return $this->hasMany(Infrastructure::class);
    }
}
