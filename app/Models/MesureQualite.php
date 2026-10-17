<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesureQualite extends Model
{
    use HasFactory;

    protected $table = 'mesures_qualites';

    /**
     * Provenance values accepted by the manual measurement forms.
     * Kept in sync with StoreMesureQualiteRequest and UpdateMesureQualiteRequest.
     */
    public const SOURCES = ['simule', 'manuel', 'laboratoire'];

    /** Values retained for compatibility with older rows and external connector documents. */
    public const LEGACY_SOURCES = ['emodnet'];

    /**
     * Provenance values used by connector providers.
     * These are not manually selectable in the forms, but may exist in imported rows.
     */
    public const PROVIDER_SOURCES = ['simulation', 'csv', 'data_gov_tn', 'emodnet'];

    /** Sources that originate from an external connector/API (never presented as real-time). */
    public const EXTERNAL_SOURCES = ['simulation', 'csv', 'data_gov_tn', 'emodnet'];

    /** Values that always represent synthetic/simulated observations. */
    public const SIMULATED_SOURCES = ['simule', 'simulation'];

    protected $fillable = [
        'reference',
        'point_mesure_id',
        'date_mesure',
        'ph',
        'turbidite',
        'chlore_residuel',
        'plomb',
        'nitrates',
        'source_donnee',
        'source_type',
        'provider',
        'external_id',
        'observation_date',
        'external_quality_flag',
        'is_verified',
        'verifier',
        'overall_compliance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_mesure'           => 'datetime',
            'observation_date'      => 'datetime',
            'ph'                    => 'decimal:2',
            'turbidite'             => 'decimal:2',
            'chlore_residuel'       => 'decimal:3',
            'plomb'                 => 'decimal:3',
            'nitrates'              => 'decimal:2',
            'is_verified'           => 'boolean',
            'overall_compliance'    => 'decimal:2',
            'external_quality_flag' => 'integer',
        ];
    }

    /** True when this observation came from an external connector/API. */
    public function isExternalSource(): bool
    {
        return in_array($this->source_donnee, self::EXTERNAL_SOURCES, true)
            || ($this->provider !== null && $this->provider !== '');
    }

    /** True when this row is simulated / synthetic, never a real field observation. */
    public function isSimulated(): bool
    {
        return $this->source_type === 'simulated'
            || in_array($this->source_donnee, self::SIMULATED_SOURCES, true);
    }

    public function pointMesure(): BelongsTo
    {
        return $this->belongsTo(PointMesure::class);
    }
}
