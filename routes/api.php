<?php

use App\Http\Controllers\Api\InfrastructureApiController;
use App\Http\Controllers\Api\InfrastructureRiskApiController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\V1\BeachController;
use App\Http\Controllers\Api\V1\BeachHistoricalAdvisoryController;
use App\Http\Controllers\Api\V1\BeachMeasurementController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Http\Controllers\Api\ZoneApiController;
use App\Http\Middleware\JwtAuthenticate;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/governorates', function (Request $request) {
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = (int) ($filters['per_page'] ?? 20);
        $governorates = Governorate::query()
            ->where('is_verified', true)
            ->where('is_published', true)
            ->orderBy('name')
            ->paginate($perPage, ['id', 'name', 'slug'], 'page', (int) ($filters['page'] ?? 1));

        return response()->json([
            'data' => $governorates->getCollection()->map(fn (Governorate $governorate): array => [
                'id' => $governorate->id,
                'name' => $governorate->name,
                'slug' => $governorate->slug,
            ])->values(),
            'meta' => [
                'current_page' => $governorates->currentPage(),
                'last_page' => $governorates->lastPage(),
                'per_page' => $governorates->perPage(),
                'total' => $governorates->total(),
            ],
        ]);
    })->name('governorates.index');

    Route::get('/beaches', [BeachController::class, 'index'])->name('beaches.index');
    Route::get('/beaches/{beach}/measurements', [BeachMeasurementController::class, 'index'])->name('beaches.measurements.index');
    Route::get('/beaches/{beach}', [BeachController::class, 'show'])->name('beaches.show');
    Route::get('/historical-advisories', [BeachHistoricalAdvisoryController::class, 'index'])->name('historical-advisories.index');
});

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

Route::middleware(JwtAuthenticate::class)->group(function (): void {
    Route::get('/points-mesure', [WaterQualityController::class, 'points']);
    Route::get('/mesures', [WaterQualityController::class, 'index']);
    Route::get('/mesures/{mesure}', [WaterQualityController::class, 'show']);
    Route::post('/mesures', [WaterQualityController::class, 'store'])
        ->middleware('jwt.role:technicien,manager,administrateur');
    Route::put('/mesures/{mesure}', [WaterQualityController::class, 'update'])
        ->middleware('jwt.role:technicien,manager,administrateur');
    Route::post('/mesures/{mesure}/validate', [WaterQualityController::class, 'validateMeasurement'])
        ->middleware('jwt.role:manager,administrateur');
    Route::delete('/mesures/{mesure}', [WaterQualityController::class, 'destroy'])
        ->middleware('jwt.role:manager,administrateur');
});

Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/zones', [ZoneApiController::class, 'index'])->name('api.zones.index');
    Route::get('/zones/{zone}', [ZoneApiController::class, 'show'])->name('api.zones.show');
    Route::get('/infrastructures', [InfrastructureApiController::class, 'index'])->name('api.infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [InfrastructureApiController::class, 'show'])->name('api.infrastructures.show');
});

Route::middleware(['web', 'role:manager,technician,admin', 'throttle:60,1'])
    ->get('/infrastructures/{infrastructure}/risk', [InfrastructureRiskApiController::class, 'show'])
    ->name('api.infrastructures.risk');
