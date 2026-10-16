<?php

namespace App\Services\ExternalData;

/**
 * Provider-agnostic representation of a single water-quality observation.
 *
 * All fields use the same units as MesureQualite:
 *   - ph              : dimensionless (0–14)
 *   - turbidite       : NTU
 *   - nitrates        : mg/L  (providers may deliver µmol/L → convert before populating)
 *   - chlore_residuel : mg/L  (null when provider does not supply it)
 *   - plomb           : µg/L / ppb (null when provider does not supply it)
 *
 * Providers MUST set external_id to a stable, unique value from the source
 * so that ExternalSyncService can detect duplicates without touching the database.
 */
final class NormalisedObservation
{
    public function __construct(
        /** Stable external identifier — used for idempotent upsert. */
        public readonly string $externalId,

        /** Provider slug (e.g. 'emodnet'). */
        public readonly string $provider,

        /** Original station/platform identifier from the provider. */
        public readonly string $stationCode,

        /** Station display name. */
        public readonly string $stationName,

        /** Latitude (degrees_north). */
        public readonly float $latitude,

        /** Longitude (degrees_east). */
        public readonly float $longitude,

        /** Actual observation timestamp (UTC). */
        public readonly \DateTimeInterface $observationDate,

        // ── Measured parameters ────────────────────────────────────────────────

        /** pH (dimensionless). Null if not available from this provider. */
        public readonly ?float $ph,

        /** Turbidity (NTU). Null if not available. */
        public readonly ?float $turbidite,

        /** Nitrates (mg/L, already converted from provider units). Null if not available. */
        public readonly ?float $nitrates,

        /** Residual chlorine (mg/L). Null when provider does not supply it. */
        public readonly ?float $chloreResiduel,

        /** Lead/Plomb (µg/L = ppb). Null when provider does not supply it. */
        public readonly ?float $plomb,

        // ── Quality metadata ───────────────────────────────────────────────────

        /**
         * SeaDataNet QV flag (or equivalent):
         *   1 = good quality,  2 = probably good,  3 = bad,
         *   4 = changed,       0 = no QC performed
         * Null when the provider does not supply a quality flag.
         */
        public readonly ?int $qualityFlag,

        /** Any extra provider-specific fields that don't map to a column. */
        public readonly array $rawMeta = [],
    ) {}

    /**
     * True only if the observation has the minimum parameters to be useful
     * (at least one of ph, turbidite, or nitrates must be non-null).
     */
    public function isUsable(): bool
    {
        return $this->ph !== null || $this->turbidite !== null || $this->nitrates !== null;
    }

    /**
     * Convenience: return a source_donnee value appropriate for this observation.
     * External providers always return their own slug (not 'manuel' or 'simule').
     */
    public function sourceDonnee(): string
    {
        return $this->provider;
    }
}
