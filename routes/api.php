<?php

use App\Http\Controllers\Api\InfrastructureApiController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Http\Controllers\Api\ZoneApiController;
use App\Http\Controllers\Api\V1\BeachController;
use App\Http\Controllers\Api\V1\BeachHistoricalAdvisoryController;
use App\Http\Controllers\Api\V1\BeachMeasurementController;
use App\Models\Governorate;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Public beach catalogue. */
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/governorates', function (Request $request) {
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 20);
        $governorates = Governorate::query()
            ->where('is_verified', true)
            ->where('is_published', true)
            ->orderBy('name')
            ->paginate($perPage, ['id', 'name', 'slug'], 'page', $page);

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

/*
|--------------------------------------------------------------------------
| Public authentication endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('login', [JwtAuthController::class, 'login']);
    Route::post('refresh', [JwtAuthController::class, 'refresh']);
});

/*
|--------------------------------------------------------------------------
| Public zone and infrastructure endpoints
|--------------------------------------------------------------------------
*/
Route::middleware('jwt.authenticate')->group(function () {

    Route::get('/zones/{zone}', [ZoneApiController::class, 'show'])
        ->name('api.zones.show');

    Route::get('/infrastructures/{infrastructure}', [InfrastructureApiController::class, 'show'])
        ->name('api.infrastructures.show');
});

// Catalogue public des infrastructures : la ressource ne contient aucune donnée de risque.
Route::get('/infrastructures', [InfrastructureApiController::class, 'index'])
    ->name('api.infrastructures.index');

// Le risque opérationnel est réservé aux gestionnaires et techniciens connectés par session.
Route::middleware(['web', 'role:manager,technician'])->get(
    '/infrastructures/{infrastructure}/risk',
    [\App\Http\Controllers\Api\InfrastructureRiskApiController::class, 'show'],
)->name('api.infrastructures.risk');

/*
|--------------------------------------------------------------------------
| Protected endpoints — valid JWT required
|--------------------------------------------------------------------------
*/
Route::middleware('jwt.authenticate')->group(function () {

    // Authentication management
    Route::post('auth/logout', [JwtAuthController::class, 'logout']);
    Route::get('auth/me', [JwtAuthController::class, 'me']);

    // Water quality — read
    Route::get('points-mesure', [WaterQualityController::class, 'points']);
    Route::get('mesures', [WaterQualityController::class, 'index']);
    Route::get('mesures/{mesure}', [WaterQualityController::class, 'show']);

    // Water quality — create and update
    Route::middleware('jwt.role:technicien,manager,administrateur')->group(function () {
        Route::post('mesures', [WaterQualityController::class, 'store']);
        Route::put('mesures/{mesure}', [WaterQualityController::class, 'update']);
    });

    // Water quality — validation and deletion
    Route::middleware('jwt.role:manager,administrateur')->group(function () {
        Route::post(
            'mesures/{mesure}/validate',
            [WaterQualityController::class, 'validateMeasurement']
        );

        Route::delete(
            'mesures/{mesure}',
            [WaterQualityController::class, 'destroy']
        );
    });

    // Current user's permissions and roles
    Route::get('user/permissions', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $user->flushPermissionCache();
        $user->load('roles.permissions');

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'roles' => $user->roles->pluck('slug'),
            'permissions' => $user->hasRole(Role::ADMINISTRATEUR)
                ? Permission::pluck('name')
                : $user->getAllPermissions()->pluck('name'),
        ]);
    });
});
