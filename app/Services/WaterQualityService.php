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

        $parameterStatuses = [];

        // Parameters are deliberately optional. A null value means "not measured",
        // never a compliant or non-compliant value of zero.
        if (array_key_exists('ph', $values) && $values['ph'] !== null) {
            $parameterStatuses['ph'] = (
                (float) $values['ph'] < $phMin || (float) $values['ph'] > $phMax
            ) ? 'Non-Compliant' : 'Compliant';
        }

        if (array_key_exists('turbidite', $values) && $values['turbidite'] !== null) {
            $turbidite = (float) $values['turbidite'];
            $parameterStatuses['turbidite'] = $turbidite > $turbMax
                ? 'Non-Compliant'
                : ($turbidite > $turbAlert ? 'Alert' : 'Compliant');
        }

        if (array_key_exists('chlore_residuel', $values) && $values['chlore_residuel'] !== null) {
            $chlore = (float) $values['chlore_residuel'];
            $parameterStatuses['chlore_residuel'] = (
                $chlore < $chloreMin || $chlore > $chloreMax
            ) ? 'Non-Compliant' : 'Compliant';
        }

        if (array_key_exists('plomb', $values) && $values['plomb'] !== null) {
            $plomb = (float) $values['plomb'];
            $parameterStatuses['plomb'] = $plomb > $plombMax
                ? 'Non-Compliant'
                : 'Compliant';
        }

        $nitratesMaximum = config('water_quality.nitrates_max');
        if ($nitratesMaximum !== null
            && array_key_exists('nitrates', $values)
            && $values['nitrates'] !== null) {
            $parameterStatuses['nitrates'] = ((float) $values['nitrates']) > (float) $nitratesMaximum
                ? 'Non-Compliant'
                : 'Compliant';
        }

        $statuses = array_values($parameterStatuses);

        if ($statuses === []) {
            $status = 'Non evalue';
            $overallCompliance = null;
        } else {
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

            $overallCompliance = round(array_sum($scoreValues) / count($scoreValues), 2);
        }

        return [
            'parameter_statuses' => $parameterStatuses,
            'status'             => $status,
            'overall_compliance' => $overallCompliance,
        ];
    }
}
