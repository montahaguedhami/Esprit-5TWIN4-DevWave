<?php

use App\Http\Controllers\Api\WaterQualityController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Water Quality Measurements API (Protected by Granular RBAC Permissions)
Route::get('/points-mesure', [WaterQualityController::class, 'points']);
Route::get('/mesures', [WaterQualityController::class, 'index']);
Route::get('/mesures/{mesure}', [WaterQualityController::class, 'show']);
Route::post('/mesures', [WaterQualityController::class, 'store']);
Route::put('/mesures/{mesure}', [WaterQualityController::class, 'update']);
Route::post('/mesures/{mesure}/validate', [WaterQualityController::class, 'validateMeasurement']);
Route::delete('/mesures/{mesure}', [WaterQualityController::class, 'destroy']);

// Current user RBAC profile & permissions check (always fetches real-time DB permissions)
Route::get('/user/permissions', function (Request $request) {
    $email = $request->header('X-User-Email') ?? $request->bearerToken() ?? session('user.email');
    if (! $email) {
        return response()->json(['message' => 'Non authentifié.'], 401);
    }

    $user = User::with('roles.permissions')->where('email', $email)->first();
    if (! $user) {
        return response()->json(['message' => 'Utilisateur introuvable.'], 404);
    }

    $user->flushPermissionCache();

    return response()->json([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'is_active' => $user->is_active,
        'roles' => $user->roles->pluck('slug'),
        'permissions' => $user->hasRole(\App\Models\Role::ADMINISTRATEUR)
            ? \App\Models\Permission::pluck('name')
            : $user->getAllPermissions()->pluck('name'),
    ]);
});
