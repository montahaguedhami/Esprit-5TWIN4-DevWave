<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Financement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'projet_id',
        'source',
        'montant',
        'date_financement',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_financement' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    /**
     * Get the projet that owns the financement.
     */
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }
}
