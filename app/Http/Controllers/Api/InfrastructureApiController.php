<?php

namespace App\Http\Controllers\Api;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Http\Controllers\Controller;
use App\Http\Resources\InfrastructureResource;
use App\Models\Infrastructure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InfrastructureApiController extends Controller
{
    /**
     * Liste paginée des infrastructures avec recherche et filtres.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = trim((string) $request->query('q', ''));
        $rawType = $request->query('type');
        $rawEtat = $request->query('etat');
        $zoneId = $request->query('zone_id');

        $type = null;
        if (is_string($rawType) && $rawType !== '') {
            $type = InfrastructureType::tryFrom($rawType);
            if (! $type) {
                $norm = strtolower(str_replace(['_', '-', ' '], '', $rawType));
                foreach (InfrastructureType::cases() as $case) {
                    if (strtolower(str_replace(['_', '-', ' '], '', $case->value)) === $norm || strtolower($case->name) === $norm) {
                        $type = $case;
                        break;
                    }
                }
            }
        }

        $etat = null;
        if (is_string($rawEtat) && $rawEtat !== '') {
            $etat = InfrastructureEtat::tryFrom($rawEtat);
            if (! $etat) {
                $norm = strtolower(str_replace(['_', '-', ' '], '', $rawEtat));
                foreach (InfrastructureEtat::cases() as $case) {
                    if (strtolower(str_replace(['_', '-', ' '], '', $case->value)) === $norm || strtolower($case->name) === $norm) {
                        $etat = $case;
                        break;
                    }
                }
            }
        }
        $zoneIdFilter = is_numeric($zoneId) ? (int) $zoneId : null;

        $allowedSorts = ['nom', 'type', 'etat', 'date_installation', 'date_prochaine_maintenance', 'created_at'];
        $rawSort = (string) $request->query('sort');
        $sort = in_array($rawSort, $allowedSorts, true) ? $rawSort : 'nom';

        $rawDirection = strtolower((string) $request->query('direction'));
        $direction = in_array($rawDirection, ['asc', 'desc'], true) ? $rawDirection : 'asc';

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $infrastructures = Infrastructure::query()
            ->with(['zone'])
            ->withCount([
                'interventions as interventions_12_mois_count' => fn ($subQuery) => $subQuery
                    ->where('date', '>=', now()->subMonths(12)),
            ])
            ->when($q !== '', fn ($query) => $query->search($q))
            ->when($type !== null, fn ($query) => $query->deType($type))
            ->when($etat !== null, fn ($query) => $query->dEtat($etat))
            ->when($zoneIdFilter !== null, fn ($query) => $query->deZone($zoneIdFilter))
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return InfrastructureResource::collection($infrastructures);
    }

    /**
     * Fiche technique complète d'une infrastructure.
     */
    public function show(Infrastructure $infrastructure): InfrastructureResource
    {
        $infrastructure->load([
            'zone',
            'historiques',
            'interventions.technicien',
            'incidents',
        ]);

        return new InfrastructureResource($infrastructure);
    }
}
