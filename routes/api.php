<?php

use App\Http\Controllers\Api\InfrastructureApiController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Http\Controllers\Api\ZoneApiController;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [JwtAuthController::class, 'login']);
    Route::post('refresh', [JwtAuthController::class, 'refresh']);
});

Route::middleware(['throttle:60,1'])->group(function (): void {
    Route::get('/zones', [ZoneApiController::class, 'index'])->name('api.zones.index');
    Route::get('/zones/{zone}', [ZoneApiController::class, 'show'])->name('api.zones.show');
    Route::get('/infrastructures', [InfrastructureApiController::class, 'index'])->name('api.infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [InfrastructureApiController::class, 'show'])->name('api.infrastructures.show');
});

Route::middleware('jwt.auth')->group(function (): void {
    Route::post('auth/logout', [JwtAuthController::class, 'logout']);
    Route::get('auth/me', [JwtAuthController::class, 'me']);

    Route::get('points-mesure', [WaterQualityController::class, 'points']);
    Route::get('mesures', [WaterQualityController::class, 'index']);
    Route::get('mesures/{mesure}', [WaterQualityController::class, 'show']);

    Route::middleware('jwt.role:technicien,manager,administrateur')->group(function (): void {
        Route::post('mesures', [WaterQualityController::class, 'store']);
        Route::put('mesures/{mesure}', [WaterQualityController::class, 'update']);
    });

    Route::middleware('jwt.role:manager,administrateur')->group(function (): void {
        Route::post('mesures/{mesure}/validate', [WaterQualityController::class, 'validateMeasurement']);
        Route::delete('mesures/{mesure}', [WaterQualityController::class, 'destroy']);
    });

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
