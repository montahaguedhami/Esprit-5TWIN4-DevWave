<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    public ?int $ancienneInfrastructureId = null;

    protected $fillable = [
        'titre',
        'description',
        'type',
        'gravite',
        'statut',
        'date_signalement',
        'localisation',
        'latitude',
        'longitude',
        'zone_id',
        'infrastructure_id',
        'user_id',
        'preuve_path',
    ];

    protected $casts = [
        'date_signalement' => 'datetime',
    ];

    public function actions()
    {
        return $this->hasMany(ActionCorrective::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function infrastructure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
