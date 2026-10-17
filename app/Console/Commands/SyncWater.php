<?php

namespace App\Console\Commands;

use App\Jobs\CollectWaterDataJob;
use App\Services\ExternalData\ExternalSyncService;
use Illuminate\Console\Command;

/**
 * Artisan command for water data collection.
 *
 * Usage:
 *   php artisan water:sync                           # runs all providers
 *   php artisan water:sync --provider=simulation     # run one provider
 *   php artisan water:sync --no-simulation           # skip simulated fill
 *   php artisan water:sync --queue                   # dispatch as background job
 *   php artisan water:sync --provider=csv --file=/path/to/data.csv
 *
 * The command is idempotent: re-running it for the same day will skip
 * already-imported observations (deduplicated by provider + external_id).
 */
class SyncWater extends Command
{
    protected $signature = 'water:sync
                            {--provider=* : Provider slug(s) to run (simulation, data_gov_tn, csv). Default: all}
                            {--no-simulation : Skip the synthetic simulation fill step}
                            {--queue : Dispatch as a background queue job instead of running synchronously}
                            {--file= : File path for the CSV/XLSX import provider}';

    protected $description = 'Collect and import water quality data from configured providers';

    public function handle(ExternalSyncService $syncService): int
    {
        $providers     = $this->option('provider') ?: [];
        $fillSimulated = !$this->option('no-simulation');
        $useQueue      = $this->option('queue');
        $filePath      = $this->option('file');

        if ($useQueue) {
            CollectWaterDataJob::dispatch(
                triggeredByUserId: null,
                providers:         $providers,
                fillSimulated:     $fillSimulated,
            );
            $this->info('Collection job dispatched to queue.');
            return self::SUCCESS;
        }

        // Handle CSV file path injection for the csv provider
        if ($filePath) {
            /** @var \App\Services\ExternalData\CsvImportProvider $csvProvider */
            $csvProvider = app(\App\Services\ExternalData\CsvImportProvider::class);
            $csvProvider->setFilePath($filePath, 'artisan_import');
            // Force csv provider
            $providers = ['csv'];
        }

        $this->info('Starting water data collection...');
        $results = $syncService->runAll($providers, $fillSimulated);

        $totalImported = 0;
        $totalSkipped  = 0;
        $totalFailed   = 0;

        foreach ($results as $result) {
            $icon = $result['failed'] > 0 ? '⚠' : '✓';
            $this->line("{$icon} [{$result['provider']}] imported={$result['imported']} skipped={$result['skipped']} failed={$result['failed']}");
            if (!empty($result['error'])) {
                $this->warn("  Error: {$result['error']}");
            }
            $totalImported += $result['imported'];
            $totalSkipped  += $result['skipped'];
            $totalFailed   += $result['failed'];
        }

        $this->info("Done — imported: {$totalImported}, skipped: {$totalSkipped}, failed: {$totalFailed}");

        return $totalFailed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
