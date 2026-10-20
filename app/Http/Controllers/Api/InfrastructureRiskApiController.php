<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Services\FailureRiskService;
use App\Services\TechnicianScopeService;
use Illuminate\Http\JsonResponse;

class InfrastructureRiskApiController extends Controller
{
    public function show(Infrastructure $infrastructure, FailureRiskService $riskService): JsonResponse
    {
        if (session('user.role') === 'technician') {
            $technicien = TechnicianScopeService::currentTechnicien();
            abort_unless(
                $technicien && TechnicianScopeService::canAccessInfrastructure($technicien, $infrastructure),
                403,
                'Accès refusé à cette infrastructure.',
            );
        }

        $prediction = $infrastructure->latestRiskPrediction;
        $risk = $prediction
            ? [
                'probability' => $prediction->probability,
                'level' => $prediction->level,
                'factors' => $prediction->factors ?? [],
            ]
            : $riskService->predict($infrastructure);

        return response()->json([
            'estimation_indicative' => true,
            'horizon_days' => 30,
            'probability' => $risk['probability'],
            'level' => $risk['level'],
            'factors' => $risk['factors'],
            'recommendation' => $riskService->recommendationFor($risk['factors'], $risk['level'], null, $infrastructure->id),
            'computed_at' => $prediction?->computed_at?->toISOString(),
        ]);
    }
}
