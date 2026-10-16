
<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\ProviderSyncLog;
use App\Models\User;
use App\Services\ExternalData\ExternalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Asynchronous water-data collection job.
 *
 * Dispatched by DataCollectionController::trigger().
 * Implements ShouldBeUnique so only one run is queued at a time.
 *
 * The job:
 *   1. Locks a cache key so the UI can show "running" state.
 *   2. Delegates to ExternalSyncService which handles provider routing.
 *   3. Records every run in provider_sync_logs.
 *   4. Releases the lock on completion or failure.
 *   5. Never exposes Ollama errors to the queue — RAG is optional.
 */
class CollectWaterDataJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Maximum number of attempts before the job is marked failed. */
    public int $tries = 2;

    /** Timeout in seconds for the entire job. */
    public int $timeout = 300;

    /** Cache key used as a global "is running?" lock for the UI. */
    public const LOCK_KEY = 'water_collect_running';

    /** How long the lock lasts (seconds) — safety net if job dies. */
    public const LOCK_TTL = 400;

    public function __construct(
        /** ID of the user who triggered the collection (for audit log). */
        public readonly ?int $triggeredByUserId = null,

        /** Specific providers to run, or empty for all configured providers. */
        public readonly array $providers = [],

        /** Whether to fill missing parameters with simulated data. */
        public readonly bool $fillSimulated = true,
    ) {}

    /**
     * Unique lock: prevent duplicate jobs from being queued simultaneously.
     * The uniqueId is the same for all dispatches, so only one job queues at once.
     */
    public function uniqueId(): string
    {
        return 'collect_water_data';
    }

    public function handle(ExternalSyncService $syncService): void
    {
        // Mark as running for the UI
        Cache::put(self::LOCK_KEY, [
            'status'    => 'running',
            'started_at'=> now()->toIso8601String(),
            'user_id'   => $this->triggeredByUserId,
        ], self::LOCK_TTL);

        $overallSuccess = true;
        $totalImported  = 0;
        $totalSkipped   = 0;
        $totalFailed    = 0;
        $errors         = [];

        try {
            $results = $syncService->runAll(
                providers: $this->providers,
                fillSimulated: $this->fillSimulated,
            );

            foreach ($results as $result) {
                $totalImported += $result['imported'] ?? 0;
                $totalSkipped  += $result['skipped'] ?? 0;
                $totalFailed   += $result['failed'] ?? 0;
                if (!empty($result['error'])) {
                    $overallSuccess = false;
                    $errors[]       = $result['provider'] . ': ' . $result['error'];
                }
            }
        } catch (\Throwable $e) {
            $overallSuccess = false;
            $errors[]       = $e->getMessage();
            Log::error('CollectWaterDataJob fatal error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        } finally {
            if (! $overallSuccess && $totalImported === 0) {
                $status = 'failed';
            } elseif (! $overallSuccess) {
                $status = 'partial';
            } elseif ($totalImported === 0) {
                $status = 'no_data';
            } else {
                $status = 'success';
            }

            Cache::put(self::LOCK_KEY, [
                'status'      => $status,
                'finished_at' => now()->toIso8601String(),
                'imported'    => $totalImported,
                'skipped'     => $totalSkipped,
                'failed'      => $totalFailed,
                'errors'      => $errors,
            ], 3600);

            // Audit log — non-blocking
            try {
                $user = $this->triggeredByUserId ? User::find($this->triggeredByUserId) : null;
                AuditLog::record('data_collection_completed', 'CollectWaterDataJob', null, [
                    'status'   => $status,
                    'imported' => $totalImported,
                    'errors'   => $errors,
                ], $user);
            } catch (\Throwable) {
                // never block queue on audit failure
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put(self::LOCK_KEY, [
            'status'      => 'failed',
            'finished_at' => now()->toIso8601String(),
            'imported'    => 0,
            'errors'      => [$exception->getMessage()],
        ], 3600);

        Log::error('CollectWaterDataJob failed after retries', [
            'message' => $exception->getMessage(),
        ]);
    }
}
