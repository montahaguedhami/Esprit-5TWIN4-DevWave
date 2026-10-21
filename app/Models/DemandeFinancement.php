<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A citizen's proposal to fund a project.
 *
 * It becomes money only once a payment has been confirmed by the gateway;
 * until then the project totals must not move.
 */
class DemandeFinancement extends Model
{
    use HasFactory;

    /**
     * Force the table name — Eloquent would otherwise pluralise the model
     * name to `demande_financements` instead of `demandes_financement`.
     */
    protected $table = 'demandes_financement';

    public const EN_ATTENTE = 'en_attente';
    public const ACCEPTEE   = 'acceptee';
    public const REFUSEE    = 'refusee';
    public const PAYEE      = 'payee';

    public const STATUTS = [
        self::EN_ATTENTE,
        self::ACCEPTEE,
        self::REFUSEE,
        self::PAYEE,
    ];

    /**
     * Statuts shown to the citizen, in display order.
     */
    public const LIBELLES = [
        self::EN_ATTENTE => 'En attente',
        self::ACCEPTEE   => 'Acceptée',
        self::REFUSEE    => 'Refusée',
        self::PAYEE      => 'Payée',
    ];

    /**
     * Project statuses that may still accept a citizen contribution.
     * Finished, suspended or cancelled projects are excluded on purpose.
     */
    public const STATUTS_PROJET_ELIGIBLE = [
        'planifie',
        'en_cours',
    ];

    protected $fillable = [
        'projet_id',
        'user_id',
        'montant',
        'devise',
        'statut',
        'decided_by',
        'decided_at',
        'motif_refus',
    ];

    protected function casts(): array
    {
        return [
            'montant'    => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    /* ── Relations ────────────────────────────────────────────────────────── */

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    /**
     * The citizen who submitted the request.
     */
    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The manager who accepted or refused it.
     */
    public function decideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'demande_id');
    }

    /* ── Aides d'état ─────────────────────────────────────────────────────── */

    public function estEnAttente(): bool
    {
        return $this->statut === self::EN_ATTENTE;
    }

    public function estAcceptee(): bool
    {
        return $this->statut === self::ACCEPTEE;
    }

    public function estRefusee(): bool
    {
        return $this->statut === self::REFUSEE;
    }

    public function estPayee(): bool
    {
        return $this->statut === self::PAYEE;
    }

    /**
     * Payable only when approved and not already settled.
     */
    public function estPayable(): bool
    {
        return $this->statut === self::ACCEPTEE;
    }

    public function libelleStatut(): string
    {
        return self::LIBELLES[$this->statut] ?? ucfirst((string) $this->statut);
    }

    /**
     * The payment attempt currently in flight, if any.
     */
    public function paiementEnCours(): ?Paiement
    {
        return $this->paiements()
            ->where('statut', Paiement::EN_ATTENTE)
            ->latest()
            ->first();
    }
}
