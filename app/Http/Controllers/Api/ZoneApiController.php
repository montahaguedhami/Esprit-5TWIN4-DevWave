<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneResource;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ZoneApiController extends Controller
{
    /**
     * Liste paginée des zones avec filtres et recherche.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = trim((string) $request->query('q', ''));
        $allowedSorts = ['nom', 'infrastructures_count', 'created_at', 'superficie_km2', 'population_desservie'];
        $rawSort = (string) $request->query('sort');
        $sort = in_array($rawSort, $allowedSorts, true) ? $rawSort : 'nom';

        $rawDirection = strtolower((string) $request->query('direction'));
        $direction = in_array($rawDirection, ['asc', 'desc'], true) ? $rawDirection : 'asc';

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $zones = Zone::query()
            ->with(['responsable'])
            ->withCount('infrastructures')
            ->when($q !== '', fn ($query) => $query->search($q))
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return ZoneResource::collection($zones);
    }

    /**
     * Détails complets d'une zone.
     */
    public function show(Zone $zone): ZoneResource
    {
        $zone->load([
            'responsable',
            'infrastructures' => fn ($q) => $q->orderBy('nom'),
            'pointsMesure',
            'projets',
        ])->loadCount('infrastructures');

        return new ZoneResource($zone);
    }
}
