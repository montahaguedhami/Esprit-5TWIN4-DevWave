<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A project saved by a citizen — the EXPLICIT interest signal.
 *
 * Favouriting is a deliberate act: the citizen chose this project. That makes
 * it a stronger signal than a consultation, which is only implicit.
 */
class Favori extends Model
{
    use HasFactory;

    /**
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'projet_id',
    ];

    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }
}
