<?php

namespace App\Services;

use App\Models\InfrastructureRiskSettings;

class InfrastructureRiskSettingsService
{
    public function configuration(): array
    {
        $defaults = config('infrastructure.risk');
        $saved = InfrastructureRiskSettings::query()->find(1)?->parameters ?? [];

        return array_replace_recursive($defaults, $saved);
    }

    public function update(array $parameters): void
    {
        InfrastructureRiskSettings::query()->updateOrCreate(
            ['id' => 1],
            ['parameters' => $parameters],
        );
    }
}
