<?php

use App\Http\Controllers\Api\InfrastructureApiController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Http\Controllers\Api\ZoneApiController;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public authentication endpoints
|--------------------------------------------------------------------------
*/
// POST login is guarded by LoginRateLimiter inside JwtAuthController::login:
// 5 failures -> 30 s lockout, and the controller has to build the 429 body
// itself (error + retry_after), which the throttle middleware cannot shape.
// refresh keeps its own plain throttle.
Route::prefix('auth')->group(function () {
    Route::post('login', [JwtAuthController::class, 'login']);

    Route::post('refresh', [JwtAuthController::class, 'refresh'])
        ->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Public zone and infrastructure endpoints
|--------------------------------------------------------------------------
*/
Route::middleware('jwt.authenticate')->group(function () {

    Route::get('/zones/{zone}', [ZoneApiController::class, 'show'])
        ->name('api.zones.show');

    Route::get('/infrastructures', [InfrastructureApiController::class, 'index'])
        ->name('api.infrastructures.index');

    Route::get('/infrastructures/{infrastructure}', [InfrastructureApiController::class, 'show'])
        ->name('api.infrastructures.show');
});

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
