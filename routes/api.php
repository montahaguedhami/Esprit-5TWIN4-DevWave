<?php

use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\WaterQualityController;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public auth endpoints (no JWT required)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('login',   [JwtAuthController::class, 'login']);
    Route::post('refresh', [JwtAuthController::class, 'refresh']);
});

/*
|--------------------------------------------------------------------------
| Protected endpoints — require valid JWT access token
|--------------------------------------------------------------------------
*/
Route::middleware('jwt.authenticate')->group(function () {

    // Auth management
    Route::post('auth/logout', [JwtAuthController::class, 'logout']);
    Route::get('auth/me',      [JwtAuthController::class, 'me']);

    // Water quality — read (any authenticated active user)
    Route::get('points-mesure',    [WaterQualityController::class, 'points']);
    Route::get('mesures',          [WaterQualityController::class, 'index']);
    Route::get('mesures/{mesure}', [WaterQualityController::class, 'show']);

    // Water quality — write (technicien, manager, administrateur)
    Route::middleware('jwt.role:technicien,manager,administrateur')->group(function () {
        Route::post('mesures',         [WaterQualityController::class, 'store']);
        Route::put('mesures/{mesure}', [WaterQualityController::class, 'update']);
    });

    // Water quality — validate/delete (manager, administrateur)
    Route::middleware('jwt.role:manager,administrateur')->group(function () {
        Route::post('mesures/{mesure}/validate', [WaterQualityController::class, 'validateMeasurement']);
        Route::delete('mesures/{mesure}',        [WaterQualityController::class, 'destroy']);
    });

    // Current user RBAC profile (re-fetched from DB — never from token claims)
    Route::get('user/permissions', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $user->flushPermissionCache();
        $user->load('roles.permissions');

        return response()->json([
            'user_id'     => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'is_active'   => $user->is_active,
            'roles'       => $user->roles->pluck('slug'),
            'permissions' => $user->hasRole(Role::ADMINISTRATEUR)
                ? Permission::pluck('name')
                : $user->getAllPermissions()->pluck('name'),
        ]);
    });
});
