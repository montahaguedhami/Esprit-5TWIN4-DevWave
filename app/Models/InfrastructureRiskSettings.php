<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfrastructureRiskSettings extends Model
{
    protected $fillable = ['id', 'parameters'];

    protected function casts(): array
    {
        return ['parameters' => 'array'];
    }
}
