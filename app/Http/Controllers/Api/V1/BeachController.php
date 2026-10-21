<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TuniBeachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeachController extends Controller
{
    public function index(Request $request, TuniBeachService $service): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'governorate' => ['sometimes', 'nullable', 'string', 'max:140'],
            'status' => ['sometimes', 'nullable', 'string', 'in:safe,restricted,prohibited,unknown'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $filters = [
            'q' => trim((string) ($filters['q'] ?? '')),
            'governorate' => trim((string) ($filters['governorate'] ?? '')),
            'status' => $filters['status'] ?? '',
            'page' => (int) ($filters['page'] ?? 1),
            'per_page' => (int) ($filters['per_page'] ?? 20),
        ];

        return response()->json($service->beaches($filters));
    }

    public function show(string $beach, TuniBeachService $service): JsonResponse
    {
        $data = $service->beach($beach);

        return $data
            ? response()->json(['data' => $data])
            : response()->json(['message' => 'Beach not found.'], 404);
    }
}
