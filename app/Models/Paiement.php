<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One attempt to settle an approved funding request.
 *
 * The outcome is produced locally by SimulatedPaymentProvider: no external
 * gateway is involved and no real money ever moves.
 */
class Paiement extends Model
{
    use HasFactory;

    public const EN_ATTENTE = 'en_attente';
    public const CONFIRME   = 'confirme';
    public const ECHOUE     = 'echoue';
    public const ANNULE     = 'annule';

    public const STATUTS = [
        self::EN_ATTENTE,
        self::CONFIRME,
        self::ECHOUE,
        self::ANNULE,
    ];

    public const LIBELLES = [
        self::EN_ATTENTE => 'En attente de vérification',
        self::CONFIRME   => 'Confirmé',
        self::ECHOUE     => 'Échoué',
        self::ANNULE     => 'Annulé',
    ];

    protected $fillable = [
        'demande_id',
        'user_id',
        'projet_id',
        'montant',
        'devise',
        'statut',
        'motif',
        'reference_interne',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'montant'      => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    /* ── Relations ────────────────────────────────────────────────────────── */

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeFinancement::class, 'demande_id');
    }

    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    /* ── États ────────────────────────────────────────────────────────────── */

    public function estConfirme(): bool
    {
        return $this->statut === self::CONFIRME;
    }

    public function estEnAttente(): bool
    {
        return $this->statut === self::EN_ATTENTE;
    }

    public function libelleStatut(): string
    {
        return self::LIBELLES[$this->statut] ?? ucfirst((string) $this->statut);
    }
}
