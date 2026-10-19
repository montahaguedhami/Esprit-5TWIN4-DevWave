<?php

namespace Tests\Unit;

use App\Models\Paiement;
use App\Services\Payments\SimulationResult;
use App\Services\Payments\SimulatedPaymentProvider;
use PHPUnit\Framework\TestCase;

/**
 * The provider is pure: it makes no HTTP call, opens no connection and reads
 * nothing but the two arguments it is given. That is exactly what makes it
 * safe to call from a contribution flow that must never touch a bank.
 */
class SimulatedPaymentProviderTest extends TestCase
{
    private function paiement(): Paiement
    {
        // A bare model is enough: the provider never queries the database.
        return new Paiement([
            'montant'          => '1000.0000',
            'devise'           => 'TND',
            'reference_interne' => 'AQS-TEST-0001',
        ]);
    }

    public function test_provider_without_scenario_choice_always_succeeds(): void
    {
        $provider = new SimulatedPaymentProvider(false);

        $this->assertFalse($provider->permetChoisirScenario());

        foreach (SimulationResult::SCENARIOS as $scenario) {
            $resultat = $provider->simuler($this->paiement(), $scenario);

            $this->assertTrue($resultat->succes, "Le scénario \"{$scenario}\" ne doit pas être honoré hors mode démo.");
            $this->assertSame(SimulationResult::SUCCES, $resultat->scenario);
        }
    }

    public function test_provider_ignores_an_out_of_whitelist_scenario(): void
    {
        $provider = new SimulatedPaymentProvider(true);

        $resultat = $provider->simuler($this->paiement(), 'volontairement-invalide');

        $this->assertSame(SimulationResult::SUCCES, $resultat->scenario);
    }

    public function test_provider_honours_each_allowed_scenario_in_demo_mode(): void
    {
        $provider = new SimulatedPaymentProvider(true);

        $attendu = [
            SimulationResult::SUCCES => true,
            SimulationResult::REFUS  => false,
            SimulationResult::ERREUR => false,
            SimulationResult::ANNULE => false,
        ];

        foreach ($attendu as $scenario => $succes) {
            $resultat = $provider->simuler($this->paiement(), $scenario);

            $this->assertSame($scenario, $resultat->scenario);
            $this->assertSame($succes, $resultat->succes, "Résultat inattendu pour \"{$scenario}\".");
            $this->assertNotSame('', trim($resultat->motif), 'Chaque résultat doit porter un motif.');
        }
    }

    public function test_technical_error_and_cancellation_are_flagged_distinctly(): void
    {
        $provider = new SimulatedPaymentProvider(true);

        $this->assertTrue($provider->simuler($this->paiement(), SimulationResult::ERREUR)->estErreurTechnique());
        $this->assertFalse($provider->simuler($this->paiement(), SimulationResult::REFUS)->estErreurTechnique());

        $this->assertTrue($provider->simuler($this->paiement(), SimulationResult::ANNULE)->estAnnule());
        $this->assertFalse($provider->simuler($this->paiement(), SimulationResult::REFUS)->estAnnule());
    }

    public function test_every_scenario_has_a_french_label(): void
    {
        foreach (SimulationResult::SCENARIOS as $scenario) {
            $this->assertArrayHasKey($scenario, SimulationResult::LIBELLES);
            $this->assertNotSame('', trim(SimulationResult::LIBELLES[$scenario]));
        }
    }
}
