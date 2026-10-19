<?php

namespace App\Services\Payments;

/**
 * Result of a LOCAL payment simulation.
 *
 * Nothing here ever talks to a network or to a bank. The object only carries
 * the decision taken by SimulatedPaymentProvider, which the service layer then
 * applies to the database inside a transaction.
 */
final class SimulationResult
{
    /**
     * The simulation succeeded: the contribution may be recorded.
     */
    public const SUCCES = 'succes';

    /**
     * The simulated payment was refused (think "insufficient funds").
     */
    public const REFUS = 'refus';

    /**
     * A simulated technical error occurred. The attempt is closed and can be
     * retried; no contribution is recorded.
     */
    public const ERREUR = 'erreur';

    /**
     * The citizen cancelled before the simulation ran.
     */
    public const ANNULE = 'annule';

    public const SCENARIOS = [
        self::SUCCES,
        self::REFUS,
        self::ERREUR,
        self::ANNULE,
    ];

    public const LIBELLES = [
        self::SUCCES => 'Simulation réussie',
        self::REFUS  => 'Simulation refusée',
        self::ERREUR => 'Erreur technique simulée',
        self::ANNULE => 'Simulation annulée',
    ];

    public function __construct(
        public readonly string $scenario,
        public readonly bool $succes,
        public readonly string $motif,
    ) {
    }

    public function libelle(): string
    {
        return self::LIBELLES[$this->scenario] ?? $this->scenario;
    }

    public function estErreurTechnique(): bool
    {
        return $this->scenario === self::ERREUR;
    }

    public function estAnnule(): bool
    {
        return $this->scenario === self::ANNULE;
    }
}
