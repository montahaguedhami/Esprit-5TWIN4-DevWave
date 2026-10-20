<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TuniBeachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeachHistoricalAdvisoryController extends Controller
{
    public function index(Request $request, TuniBeachService $service): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'governorate' => ['sometimes', 'nullable', 'string', 'max:140'],
            'status' => ['sometimes', 'nullable', 'string', 'in:restricted,unknown'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json($service->historicalAdvisories([
            'q' => trim((string) ($validated['q'] ?? '')),
            'governorate' => trim((string) ($validated['governorate'] ?? '')),
            'status' => $validated['status'] ?? '',
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ]));
    }
}
