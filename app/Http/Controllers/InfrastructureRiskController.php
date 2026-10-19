<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Services\AiExplanationService;
use App\Services\FailureRiskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InfrastructureRiskController extends Controller
{
    public function show(Infrastructure $infrastructure, FailureRiskService $riskService, AiExplanationService $aiExplanation): View
    {
        $prediction = $infrastructure->latestRiskPrediction;
        $risk = $prediction
            ? [
                'probability' => $prediction->probability,
                'level' => $prediction->level,
                'factors' => $prediction->factors ?? [],
            ]
            : $riskService->predict($infrastructure);
        $risk['recommendation'] = $riskService->recommendationFor($risk['factors'], $risk['level'], null, $infrastructure->id);
        $fallback = $this->fallbackExplanation($risk['factors']);
        $risk['explanation'] = $aiExplanation->explain($infrastructure->id, $risk['factors'], $fallback);

        $history = $infrastructure->riskPredictions()
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        return view('manager.infrastructures.risk', compact('infrastructure', 'risk', 'history'));
    }

    public function recompute(Infrastructure $infrastructure, FailureRiskService $riskService): RedirectResponse
    {
        $risk = $riskService->recompute($infrastructure);
        Log::info('Recalcul manuel du risque d’infrastructure.', [
            'actor' => session('user.email'),
            'actor_role' => session('user.role'),
            'infrastructure_id' => $infrastructure->id,
            'probability' => $risk['probability'],
            'level' => $risk['level'],
        ]);

        return back()->with('success', 'Estimation du risque recalculée.');
    }

    public function recomputeAll(Request $request, FailureRiskService $riskService): RedirectResponse
    {
        $processed = 0;
        Infrastructure::query()->chunkById(100, function ($infrastructures) use ($riskService, &$processed): void {
            foreach ($infrastructures as $infrastructure) {
                $riskService->recompute($infrastructure);
                $processed++;
            }
        });

        Log::info('Recalcul global du risque d’infrastructure depuis l’interface liste.', [
            'actor' => session('user.email'),
            'actor_role' => session('user.role'),
            'infrastructures_count' => $processed,
        ]);

        return back()->with('success', "Modèle de risque recalculé pour {$processed} infrastructure(s).");
    }

    private function fallbackExplanation(array $factors): string
    {
        $explanations = array_slice(array_column($factors, 'explanation'), 0, 2);
        if ($explanations === []) {
            return 'Les données disponibles indiquent un niveau de risque estimatif pour cette infrastructure. Complétez les informations de maintenance pour affiner cette estimation.';
        }

        return implode('. ', array_map(
            static fn (string $explanation): string => rtrim($explanation, '. '),
            $explanations,
        )) . '.';
    }
}
