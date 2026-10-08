<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionCorrective extends Model
{
    use HasFactory;

    protected $table = 'action_correctives';

    protected $fillable = [
        'incident_id',
        'titre',
        'description',
        'responsable',
        'date_prevue',
        'date_realisation',
        'statut',
        'resultat',
    ];

    protected $casts = [
        'date_prevue' => 'date',
        'date_realisation' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
