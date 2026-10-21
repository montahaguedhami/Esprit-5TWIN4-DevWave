<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TuniBeachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeachMeasurementController extends Controller
{
    public function index(string $beach, Request $request, TuniBeachService $service): JsonResponse
    {
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $data = $service->measurements(
            $beach,
            (int) ($filters['page'] ?? 1),
            (int) ($filters['per_page'] ?? 20),
        );

        return $data
            ? response()->json($data)
            : response()->json(['message' => 'Beach not found.'], 404);
    }
}
