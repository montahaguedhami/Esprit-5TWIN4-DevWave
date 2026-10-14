<?php

namespace App\Services;

class WaterQualityService
{
    /**
     * Calculate parameter status, overall status, and a compliance score.
     *
     * All thresholds are read from config/water_quality.php.
     * This method is the single authoritative entry-point for measurement evaluation.
     *
     * @param  array<string, int|float|string>  $values
     * @return array{parameter_statuses: array<string, string>, status: string, overall_compliance: float}
     */
    public function assess(array $values): array
    {
        $phMin      = (float) config('water_quality.ph_min', 6.5);
        $phMax      = (float) config('water_quality.ph_max', 8.5);
        $turbAlert  = (float) config('water_quality.turbidite_alert', 1.0);
        $turbMax    = (float) config('water_quality.turbidite_max', 2.0);
        $chloreMin  = (float) config('water_quality.chlore_min', 0.2);
        $chloreMax  = (float) config('water_quality.chlore_max', 2.0);
        $plombMax   = (float) config('water_quality.plomb_max', 15.0);

        $parameterStatuses = [
            'ph' => ($values['ph'] < $phMin || $values['ph'] > $phMax)
                ? 'Non-Compliant'
                : 'Compliant',

            'turbidite' => $values['turbidite'] > $turbMax
                ? 'Non-Compliant'
                : ($values['turbidite'] > $turbAlert ? 'Alert' : 'Compliant'),

            'chlore_residuel' => ($values['chlore_residuel'] < $chloreMin || $values['chlore_residuel'] > $chloreMax)
                ? 'Non-Compliant'
                : 'Compliant',

            'plomb' => $values['plomb'] > $plombMax
                ? 'Non-Compliant'
                : 'Compliant',
        ];

        $nitratesMaximum = config('water_quality.nitrates_max');
        if ($nitratesMaximum !== null) {
            $parameterStatuses['nitrates'] = $values['nitrates'] > $nitratesMaximum
                ? 'Non-Compliant'
                : 'Compliant';
        }

        $statuses = array_values($parameterStatuses);
        $status = in_array('Non-Compliant', $statuses, true)
            ? 'Non-Compliant'
            : (in_array('Alert', $statuses, true) ? 'Alert' : 'Compliant');

        $scoreValues = array_map(
            static fn (string $parameterStatus): int => match ($parameterStatus) {
                'Non-Compliant' => 0,
                'Alert'         => 60,
                default         => 100,
            },
            $statuses
        );

        return [
            'parameter_statuses' => $parameterStatuses,
            'status'             => $status,
            'overall_compliance' => round(array_sum($scoreValues) / count($scoreValues), 2),
        ];
    }
}
