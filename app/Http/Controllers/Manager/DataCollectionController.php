<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Jobs\CollectWaterDataJob;
use App\Models\PointMesure;
use App\Models\ProviderSyncLog;
use App\Services\ExternalData\ExternalSyncService;
use App\Services\RagAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Handles data collection triggering and status polling for the quality dashboard.
 *
 * Routes (added to web.php under role:manager middleware):
 *   POST  /manager/data-collection/trigger      → trigger()
 *   GET   /manager/data-collection/status       → status()
 *   GET   /manager/data-collection/history      → history()
 *   POST  /manager/data-collection/import-csv   → importCsv()
 *   GET   /manager/quality/{point}/analyse      → analysePoint()
 */
class DataCollectionController extends Controller
{
    public function __construct(
        private readonly ExternalSyncService $syncService,
        private readonly RagAnalysisService $ragService,
    ) {}

    /**
     * Trigger an asynchronous data collection run.
     *
     * Requires: measurements.create permission (technicien or admin trigger;
     * manager can also trigger via dashboard).
     * Prevents duplicate runs via ShouldBeUnique on the job.
     */
    public function trigger(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Managers and admins can trigger; technicians cannot
        if ($user && !$user->hasRole(['manager', 'administrateur'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        // Check if already running
        $current = Cache::get(CollectWaterDataJob::LOCK_KEY);
        if ($current && ($current['status'] ?? '') === 'running') {
            return response()->json([
                'message'    => 'Une collecte est déjà en cours.',
                'status'     => 'running',
                'started_at' => $current['started_at'] ?? null,
            ], 409);
        }

        $fillSimulated = (bool) $request->input('fill_simulated', true);
        $providers     = (array) $request->input('providers', []);

        CollectWaterDataJob::dispatch(
            triggeredByUserId: $user->id,
            providers:         $providers,
            fillSimulated:     $fillSimulated,
        );

        // Immediately mark as queued for the UI
        Cache::put(CollectWaterDataJob::LOCK_KEY, [
            'status'    => 'queued',
            'started_at'=> now()->toIso8601String(),
            'user_id'   => $user->id,
        ], CollectWaterDataJob::LOCK_TTL);

        return response()->json([
            'message' => 'Collecte planifiée avec succès.',
            'status'  => 'queued',
        ]);
    }

    /**
     * Poll the current collection status (for the UI progress indicator).
     */
    public function status(): JsonResponse
    {
        $lock = Cache::get(CollectWaterDataJob::LOCK_KEY);

        return response()->json([
            'job'      => $lock ?? ['status' => 'idle'],
            'providers'=> $this->syncService->syncStatusSummary(),
            'stats'    => [
                'total_points'  => PointMesure::where('statut', 'actif')->count(),
                'total_measures'=> \App\Models\MesureQualite::count(),
            ],
        ]);
    }

    /**
     * Return the last N sync log entries for display in the history panel.
     */
    public function history(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 20), 100);

        $logs = ProviderSyncLog::orderByDesc('started_at')
            ->limit($limit)
            ->get(['id', 'provider', 'dataset_id', 'status', 'records_fetched',
                   'records_imported', 'records_skipped', 'records_failed',
                   'error_message', 'started_at', 'finished_at']);

        return response()->json(['data' => $logs]);
    }

    /**
     * Manual CSV/XLSX import endpoint.
     *
     * Accepts a file upload and processes it synchronously (small files only).
     * Large files should be handled via queue — not implemented here.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user && !$user->hasRole(['manager', 'administrateur'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'file'        => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
            'source_name' => ['sometimes', 'string', 'max:100'],
        ]);

        $file       = $request->file('file');
        $sourceName = $request->input('source_name', 'import_manuel');

        $path = $file->storeAs('imports', $file->getClientOriginalName(), 'local');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

        /** @var \App\Services\ExternalData\CsvImportProvider $csvProvider */
        $csvProvider = app(\App\Services\ExternalData\CsvImportProvider::class);
        $csvProvider->setFilePath($fullPath, $sourceName);

        try {
            $result = $this->syncService->runProvider($csvProvider);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => "Erreur lors de l'import des données : {$e->getMessage()}",
                'result'  => null,
            ], 500);
        }

        // Clean up uploaded file
        @unlink($fullPath);

        return response()->json([
            'message'  => "Import terminé : {$result['imported']} importé(s), {$result['skipped']} ignoré(s).",
            'result'   => $result,
        ]);
    }

    /**
     * RAG analysis for a specific measurement point.
     *
     * GET /manager/quality/{point}/analyse?days=30
     */
    public function analysePoint(Request $request, \App\Models\PointMesure $point): JsonResponse
    {
        $user = auth()->user();
        if ($user && !$user->hasRole(['manager', 'administrateur', 'technicien'])) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $days = min((int) $request->input('days', 30), 365);

        $analysis = $this->ragService->analysePoint($point, $days);

        return response()->json([
            'point'    => ['id' => $point->id, 'code' => $point->code, 'nom' => $point->nom],
            'analysis' => $analysis,
        ]);
    }
}
