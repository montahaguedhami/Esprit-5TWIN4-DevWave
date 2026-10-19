<?php

use App\Http\Controllers\Api\InfrastructureApiController;
use App\Http\Controllers\Api\InfrastructureRiskApiController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\ZoneApiController;
use App\Http\Middleware\JwtAuthenticate;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('api.auth.')->group(function (): void {
    Route::post('/login', [JwtAuthController::class, 'login'])->middleware('throttle:5,1')->name('login');
    Route::post('/refresh', [JwtAuthController::class, 'refresh'])->middleware('throttle:5,1')->name('refresh');

    Route::middleware(JwtAuthenticate::class)->group(function (): void {
        Route::post('/logout', [JwtAuthController::class, 'logout'])->name('logout');
        Route::get('/me', [JwtAuthController::class, 'me'])->name('me');
    });
});

Route::get('/user/permissions', [JwtAuthController::class, 'permissions'])
    ->middleware(JwtAuthenticate::class)
    ->name('api.user.permissions');

// Water quality API: all access requires a valid JWT.
Route::middleware(JwtAuthenticate::class)->group(function (): void {
    Route::get('/points-mesure', [WaterQualityController::class, 'points']);
    Route::get('/mesures', [WaterQualityController::class, 'index']);
    Route::get('/mesures/{mesure}', [WaterQualityController::class, 'show']);
    Route::post('/mesures', [WaterQualityController::class, 'store'])
        ->middleware('jwt.role:technicien,manager,administrateur');
});

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
