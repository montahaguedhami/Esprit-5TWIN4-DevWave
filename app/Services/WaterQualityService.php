<?php

namespace App\Services;

class WaterQualityService
{
    /**
     * Calculate parameter status, overall status, and a score from configured limits.
     *
     * @param  array<string, int|float|string>  $values
     * @return array{parameter_statuses: array<string, string>, status: string, overall_compliance: float}
     */
    public function assess(array $values): array
    {
        $parameterStatuses = [
            'ph' => $values['ph'] < 6.5 || $values['ph'] > 8.5 ? 'Non-Compliant' : 'Compliant',
            'turbidite' => $values['turbidite'] > 2
                ? 'Non-Compliant'
                : ($values['turbidite'] > 1 ? 'Alert' : 'Compliant'),
            'chlore_residuel' => $values['chlore_residuel'] < 0.2 || $values['chlore_residuel'] > 2
                ? 'Non-Compliant'
                : 'Compliant',
            'plomb' => $values['plomb'] > 15 ? 'Non-Compliant' : 'Compliant',
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
                'Alert' => 60,
                default => 100,
            },
            $statuses
        );

        return [
            'parameter_statuses' => $parameterStatuses,
            'status' => $status,
            'overall_compliance' => round(array_sum($scoreValues) / count($scoreValues), 2),
        ];
    }
}
