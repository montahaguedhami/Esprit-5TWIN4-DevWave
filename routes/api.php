<?php

use App\Http\Controllers\Api\WaterQualityController;
use Illuminate\Support\Facades\Route;

Route::get('/points-mesure', [WaterQualityController::class, 'points']);
Route::get('/mesures', [WaterQualityController::class, 'index']);
Route::get('/mesures/{mesure}', [WaterQualityController::class, 'show']);
Route::post('/mesures', [WaterQualityController::class, 'store']);
