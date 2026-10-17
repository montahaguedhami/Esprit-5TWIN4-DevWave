<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Records the outcome of each external data synchronisation run.
 *
 * @property int         $id
 * @property string      $provider
 * @property string|null $dataset_id
 * @property string      $status          running|success|partial|failed
 * @property int         $records_fetched
 * @property int         $records_imported
 * @property int         $records_skipped
 * @property int         $records_failed
 * @property string|null $error_message
 * @property array|null  $meta
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 */
class ProviderSyncLog extends Model
{
    protected $fillable = [
        'provider',
        'dataset_id',
        'status',
        'records_fetched',
        'records_imported',
        'records_skipped',
        'records_failed',
        'error_message',
        'meta',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'meta'        => 'array',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** Mark the run as finished with the given status. */
    public function finish(string $status, ?string $errorMessage = null): void
    {
        $this->update([
            'status'        => $status,
            'error_message' => $errorMessage,
            'finished_at'   => now(),
        ]);
    }
}
