<?php

namespace Tests\Unit;

use App\Services\WaterQualityService;
use Tests\TestCase;

class WaterQualityServiceTest extends TestCase
{
    private WaterQualityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WaterQualityService();
        config(['water_quality.nitrates_max' => null]);
    }

    /**
     * @return array<string, int|float>
     */
    private function compliantValues(): array
    {
        return [
            'ph' => 7.2,
            'turbidite' => 0.5,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 10,
        ];
    }

    public function test_all_compliant_parameters_yield_compliant_status_and_full_score(): void
    {
        $result = $this->service->assess($this->compliantValues());

        $this->assertSame('Compliant', $result['status']);
        $this->assertSame(100.0, $result['overall_compliance']);
        $this->assertSame('Compliant', $result['parameter_statuses']['ph']);
        $this->assertSame('Compliant', $result['parameter_statuses']['turbidite']);
        $this->assertSame('Compliant', $result['parameter_statuses']['chlore_residuel']);
        $this->assertSame('Compliant', $result['parameter_statuses']['plomb']);
        $this->assertArrayNotHasKey('nitrates', $result['parameter_statuses']);
    }

    public function test_ph_boundaries_are_inclusive(): void
    {
        $low = $this->service->assess(array_merge($this->compliantValues(), ['ph' => 6.5]));
        $high = $this->service->assess(array_merge($this->compliantValues(), ['ph' => 8.5]));
        $tooLow = $this->service->assess(array_merge($this->compliantValues(), ['ph' => 6.49]));
        $tooHigh = $this->service->assess(array_merge($this->compliantValues(), ['ph' => 8.51]));

        $this->assertSame('Compliant', $low['parameter_statuses']['ph']);
        $this->assertSame('Compliant', $high['parameter_statuses']['ph']);
        $this->assertSame('Non-Compliant', $tooLow['parameter_statuses']['ph']);
        $this->assertSame('Non-Compliant', $tooHigh['parameter_statuses']['ph']);
        $this->assertSame('Non-Compliant', $tooLow['status']);
        $this->assertSame(75.0, $tooLow['overall_compliance']);
    }

    public function test_turbidity_uses_alert_band_between_one_and_two(): void
    {
        $compliant = $this->service->assess(array_merge($this->compliantValues(), ['turbidite' => 1]));
        $alert = $this->service->assess(array_merge($this->compliantValues(), ['turbidite' => 1.5]));
        $alertAtTwo = $this->service->assess(array_merge($this->compliantValues(), ['turbidite' => 2]));
        $nonCompliant = $this->service->assess(array_merge($this->compliantValues(), ['turbidite' => 2.01]));

        $this->assertSame('Compliant', $compliant['parameter_statuses']['turbidite']);
        $this->assertSame('Alert', $alert['parameter_statuses']['turbidite']);
        $this->assertSame('Alert', $alert['status']);
        $this->assertSame(90.0, $alert['overall_compliance']);
        $this->assertSame('Alert', $alertAtTwo['parameter_statuses']['turbidite']);
        $this->assertSame('Non-Compliant', $nonCompliant['parameter_statuses']['turbidite']);
        $this->assertSame('Non-Compliant', $nonCompliant['status']);
    }

    public function test_residual_chlorine_boundaries_are_inclusive(): void
    {
        $low = $this->service->assess(array_merge($this->compliantValues(), ['chlore_residuel' => 0.2]));
        $high = $this->service->assess(array_merge($this->compliantValues(), ['chlore_residuel' => 2]));
        $tooLow = $this->service->assess(array_merge($this->compliantValues(), ['chlore_residuel' => 0.19]));
        $tooHigh = $this->service->assess(array_merge($this->compliantValues(), ['chlore_residuel' => 2.01]));

        $this->assertSame('Compliant', $low['parameter_statuses']['chlore_residuel']);
        $this->assertSame('Compliant', $high['parameter_statuses']['chlore_residuel']);
        $this->assertSame('Non-Compliant', $tooLow['parameter_statuses']['chlore_residuel']);
        $this->assertSame('Non-Compliant', $tooHigh['parameter_statuses']['chlore_residuel']);
    }

    public function test_lead_threshold_is_inclusive(): void
    {
        $ok = $this->service->assess(array_merge($this->compliantValues(), ['plomb' => 15]));
        $bad = $this->service->assess(array_merge($this->compliantValues(), ['plomb' => 15.01]));

        $this->assertSame('Compliant', $ok['parameter_statuses']['plomb']);
        $this->assertSame('Non-Compliant', $bad['parameter_statuses']['plomb']);
    }

    public function test_non_compliant_overrides_alert_for_overall_status(): void
    {
        $result = $this->service->assess(array_merge($this->compliantValues(), [
            'turbidite' => 1.5,
            'ph' => 5,
        ]));

        $this->assertSame('Alert', $result['parameter_statuses']['turbidite']);
        $this->assertSame('Non-Compliant', $result['parameter_statuses']['ph']);
        $this->assertSame('Non-Compliant', $result['status']);
        $this->assertSame(65.0, $result['overall_compliance']);
    }

    public function test_nitrates_are_evaluated_only_when_a_maximum_is_configured(): void
    {
        $values = array_merge($this->compliantValues(), ['nitrates' => 25]);

        config(['water_quality.nitrates_max' => 30]);
        $withinLimit = $this->service->assess($values);
        $this->assertSame('Compliant', $withinLimit['parameter_statuses']['nitrates']);
        $this->assertSame('Compliant', $withinLimit['status']);
        $this->assertSame(100.0, $withinLimit['overall_compliance']);

        config(['water_quality.nitrates_max' => 20]);
        $overLimit = $this->service->assess($values);
        $this->assertSame('Non-Compliant', $overLimit['parameter_statuses']['nitrates']);
        $this->assertSame('Non-Compliant', $overLimit['status']);
        $this->assertSame(80.0, $overLimit['overall_compliance']);
    }

    public function test_score_is_rounded_to_two_decimals(): void
    {
        config(['water_quality.nitrates_max' => 20]);

        $result = $this->service->assess(array_merge($this->compliantValues(), [
            'turbidite' => 1.5,
            'nitrates' => 10,
        ]));

        $this->assertSame('Alert', $result['status']);
        $this->assertSame(92.0, $result['overall_compliance']);
    }
}
