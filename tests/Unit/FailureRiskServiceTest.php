<?php

namespace Tests\Unit;

use App\Models\Infrastructure;
use App\Services\FailureRiskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FailureRiskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_probability_is_bounded_and_old_overdue_broken_infrastructure_is_riskier(): void
    {
        $service = app(FailureRiskService::class);
        $new = new Infrastructure([
            'etat' => 'En service',
            'date_installation' => now()->subYear()->toDateString(),
            'date_prochaine_maintenance' => now()->addMonths(6)->toDateString(),
            'duree_vie_annees' => 30,
            'niveau_criticite' => 'faible',
        ]);
        $old = new Infrastructure([
            'etat' => 'Hors service',
            'date_installation' => now()->subYears(20)->toDateString(),
            'date_prochaine_maintenance' => now()->subDays(120)->toDateString(),
            'duree_vie_annees' => 10,
            'niveau_criticite' => 'critique',
        ]);

        $newRisk = $service->predict($new);
        $oldRisk = $service->predict($old);

        $this->assertGreaterThanOrEqual(0, $newRisk['probability']);
        $this->assertLessThanOrEqual(100, $newRisk['probability']);
        $this->assertGreaterThan($newRisk['probability'], $oldRisk['probability']);
    }

    public function test_missing_data_does_not_crash_and_reduces_confidence(): void
    {
        $risk = app(FailureRiskService::class)->predict(new Infrastructure());

        $this->assertGreaterThanOrEqual(0, $risk['probability']);
        $this->assertLessThanOrEqual(100, $risk['probability']);
        $this->assertSame(0, $risk['confidence']);
        $this->assertSame([], $risk['factors']);
    }
}
