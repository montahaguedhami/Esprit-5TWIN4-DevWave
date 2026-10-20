<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\InfrastructureRiskPrediction;
use App\Services\FailureRiskService;
use App\Services\InfrastructureRiskSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InfrastructureRiskSettingsController extends Controller
{
    public function index(InfrastructureRiskSettingsService $settingsService): View
    {
        return view('admin.infrastructure-risk-settings', [
            'riskConfig' => $settingsService->configuration(),
        ]);
    }

    public function update(Request $request, InfrastructureRiskSettingsService $settingsService): RedirectResponse
    {
        $weights = array_keys(config('infrastructure.risk.weights'));
        $validated = $request->validate([
            'bias' => ['required', 'numeric', 'between:-10,10'],
            'thresholds.moyen' => ['required', 'integer', 'between:1,98'],
            'thresholds.eleve' => ['required', 'integer', 'between:2,99'],
            'thresholds.critique' => ['required', 'integer', 'between:3,100'],
            ...collect($weights)->mapWithKeys(fn (string $weight): array => ["weights.{$weight}" => ['required', 'numeric', 'between:0,10']])->all(),
        ]);

        $thresholds = $validated['thresholds'];
        if (! ($thresholds['moyen'] < $thresholds['eleve'] && $thresholds['eleve'] < $thresholds['critique'])) {
            throw ValidationException::withMessages([
                'thresholds' => 'Les seuils doivent respecter l’ordre Moyen < Élevé < Critique.',
            ]);
        }

        $settingsService->update([
            'bias' => (float) $validated['bias'],
            'weights' => array_map('floatval', $validated['weights']),
            'thresholds' => array_map('intval', $thresholds),
        ]);

        Log::info('Paramètres du modèle de risque mis à jour.', [
            'actor' => session('user.email'),
            'weights' => $validated['weights'],
            'thresholds' => $thresholds,
        ]);

        return back()->with('success', 'Paramètres du modèle de risque enregistrés.');
    }

    public function recomputeAll(FailureRiskService $riskService): RedirectResponse
    {
        $processed = 0;
        Infrastructure::query()->chunkById(100, function ($infrastructures) use ($riskService, &$processed): void {
            foreach ($infrastructures as $infrastructure) {
                $riskService->recompute($infrastructure);
                $processed++;
            }
        });

        Log::info('Recalcul global des risques d’infrastructure.', [
            'actor' => session('user.email'),
            'infrastructures_count' => $processed,
        ]);

        return back()->with('success', "Risque recalculé pour {$processed} infrastructure(s).");
    }

    public function distribution(): array
    {
        $latestIds = InfrastructureRiskPrediction::query()
            ->selectRaw('MAX(id)')
            ->groupBy('infrastructure_id');
        $counts = InfrastructureRiskPrediction::query()
            ->whereIn('id', $latestIds)
            ->selectRaw('level, COUNT(*) as total')
            ->groupBy('level')
            ->pluck('total', 'level');

        return collect(['Faible', 'Moyen', 'Élevé', 'Critique'])
            ->mapWithKeys(fn (string $level): array => [$level => (int) ($counts[$level] ?? 0)])
            ->all();
    }
}
