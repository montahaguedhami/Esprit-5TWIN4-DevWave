<?php

namespace App\Services\Payments;

use App\Models\Paiement;

/**
 * LOCAL payment simulator. No external gateway, no HTTP call, no bank, no
 * money. Purely an academic demonstration: this class decides an outcome using
 * data it already has, and nothing else.
 *
 * Which outcome is produced is decided SERVER SIDE only:
 *
 *   - production / any environment where the demo switch is off
 *       => always SimulationResult::SUCCES. There is no financial risk, so
 *          there is no reason to randomly fail a citizen's contribution.
 *
 *   - local or testing, with PAYMENT_SIMULATION_SCENARIO_CHOICE=true
 *       => the scenario picked by the citizen on the recap screen is honoured,
 *          which is what makes the "refused", "technical error" and "cancelled"
 *          cases demonstrable during a defence.
 *
 * The citizen can never reach this class without going through the controller,
 * which re-checks ownership, role and request status first.
 */
final class SimulatedPaymentProvider
{
    public function __construct(
        private readonly bool $scenarioChoiceAllowed,
    ) {
    }

    /**
     * Decide the outcome of a simulated payment.
     *
     * @param  string|null  $scenarioDemande  scenario requested by the citizen,
     *                                       already validated against the
     *                                       whitelist by the controller
     */
    public function simuler(Paiement $paiement, ?string $scenarioDemande = null): SimulationResult
    {
        $scenario = $this->resoudreScenario($paiement, $scenarioDemande);

        return new SimulationResult(
            scenario: $scenario,
            succes: $scenario === SimulationResult::SUCCES,
            motif: $this->motif($scenario),
        );
    }

    /**
     * True when the citizen is allowed to pick the outcome. Never true outside
     * development and testing.
     */
    public function permetChoisirScenario(): bool
    {
        return $this->scenarioChoiceAllowed;
    }

    private function resoudreScenario(Paiement $paiement, ?string $scenarioDemande): string
    {
        if ($this->scenarioChoiceAllowed && in_array($scenarioDemande, SimulationResult::SCENARIOS, true)) {
            return $scenarioDemande;
        }

        // No citizen input is trusted outside the demo switch, and no random
        // draw is made either: the result is stable and auditable.
        return SimulationResult::SUCCES;
    }

    private function motif(string $scenario): string
    {
        return match ($scenario) {
            SimulationResult::SUCCES  => 'Simulation de paiement acceptée.',
            SimulationResult::REFUS   => 'Simulation de paiement refusée par le système simulé.',
            SimulationResult::ERREUR  => 'Une erreur technique est survenue pendant la simulation.',
            SimulationResult::ANNULE  => 'La simulation a été annulée avant traitement.',
            default                    => 'Simulation de paiement.',
        };
    }
}
