<?php

use App\Http\Controllers\Api\InfrastructureApiController;
use App\Http\Controllers\Api\InfrastructureRiskApiController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Http\Controllers\Api\ZoneApiController;
use Illuminate\Support\Facades\Route;

// Endpoints existants qualité
Route::get('/points-mesure', [WaterQualityController::class, 'points']);
Route::get('/mesures', [WaterQualityController::class, 'index']);
Route::get('/mesures/{mesure}', [WaterQualityController::class, 'show']);
Route::post('/mesures', [WaterQualityController::class, 'store']);

// Endpoints REST AquaSecure - Zones & Infrastructures (Rate limited à 60 req/min)
Route::middleware(['throttle:60,1'])->group(function (): void {
    // Zones
    Route::get('/zones', [ZoneApiController::class, 'index'])->name('api.zones.index');
    Route::get('/zones/{zone}', [ZoneApiController::class, 'show'])->name('api.zones.show');

    // Infrastructures
    Route::get('/infrastructures', [InfrastructureApiController::class, 'index'])->name('api.infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [InfrastructureApiController::class, 'show'])->name('api.infrastructures.show');
});

Route::middleware(['web', 'role:manager,technician,admin', 'throttle:60,1'])
    ->get('/infrastructures/{infrastructure}/risk', [InfrastructureRiskApiController::class, 'show'])
    ->name('api.infrastructures.risk');
