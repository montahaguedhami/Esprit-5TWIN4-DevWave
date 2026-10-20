<?php

/**
 * AquaSecure — Water Quality Thresholds
 *
 * These values are project-configured thresholds used by WaterQualityService::assess().
 * They are informed by standards such as WHO guidelines and Tunisian ANCSEP norms, but
 * their regulatory compliance for a specific deployment has NOT been independently
 * verified. Treat them as operational defaults until a formal regulatory review is done.
 *
 * To override without modifying this file, set the corresponding env variables in .env.
 * WaterQualityService is the single source of truth for evaluation logic.
 */
return [

    // pH — acceptable drinking water range
    'ph_min' => (float) env('WATER_QUALITY_PH_MIN', 6.5),
    'ph_max' => (float) env('WATER_QUALITY_PH_MAX', 8.5),

    // Turbidity — NTU (alert above 1, non-compliant above 2)
    'turbidite_alert' => (float) env('WATER_QUALITY_TURBIDITE_ALERT', 1.0),
    'turbidite_max'   => (float) env('WATER_QUALITY_TURBIDITE_MAX', 2.0),

    // Residual chlorine — mg/L
    'chlore_min' => (float) env('WATER_QUALITY_CHLORE_MIN', 0.2),
    'chlore_max' => (float) env('WATER_QUALITY_CHLORE_MAX', 2.0),

    // Lead — ppb (action level)
    'plomb_max' => (float) env('WATER_QUALITY_PLOMB_MAX', 15.0),

    // Nitrates — mg/L (null = not evaluated)
    'nitrates_max' => env('WATER_QUALITY_NITRATES_MAX') !== null
        ? (float) env('WATER_QUALITY_NITRATES_MAX')
        : null,

];
