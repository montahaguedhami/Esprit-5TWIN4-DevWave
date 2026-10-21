<?php

namespace App\Http\Controllers\Manager;

use App\Data\PlaceholderData;
use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\PointMesure;
use App\Models\Zone;
use App\Services\WaterQualityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __invoke(Request $request, WaterQualityService $waterQualityService): View
    {
        if (! session('user')) {
            session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
        }

        $user = session('user', ['name' => 'Gestionnaire', 'role' => 'manager']);
        $isAdmin = in_array($user['role'] ?? null, ['admin', 'administrateur'], true);
        $routePrefix = $isAdmin ? 'admin' : 'manager';

        // Zones réelles géolocalisées
        $realZones = Zone::with(['responsable', 'infrastructures'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $zonesData = $realZones->isNotEmpty()
            ? $realZones->map(function (Zone $zone) use ($routePrefix) {
                return [
                    'id' => (string) $zone->id,
                    'name' => $zone->nom,
                    'nom' => $zone->nom,
                    'address' => $zone->localisation,
                    'lat' => (float) $zone->latitude,
                    'lng' => (float) $zone->longitude,
                    'status' => $zone->statut?->value === 'inactive' ? 'alert' : 'normal',
                    'statut' => $zone->statut?->value ?? 'active',
                    'responsable' => $zone->responsable?->name ?? 'Non assigné',
                    'infrastructures_count' => $zone->infrastructures->count(),
                    'population' => (int) ($zone->population_desservie ?? 0),
                    'superficie' => (float) ($zone->superficie_km2 ?? 0),
                    'quality' => 95,
                    'pressure' => 3.5,
                    'flowRate' => 120,
                    'incidents' => $zone->incidents()->whereNotIn('statut', ['resolu', 'cloture'])->count(),
                    'lastUpdate' => 'récent',
                    'technician' => $zone->responsable?->name ?? 'Équipe locale',
                    'emoji' => '📍',
                    'color' => '#06b6d4',
                    'show_url' => route($routePrefix . '.zones.show', $zone),
                ];
            })->values()->toArray()
            : array_map(function ($z) {
                $z['nom'] = $z['name'] ?? 'Zone';
                $z['statut'] = ($z['status'] ?? '') === 'critical' ? 'inactive' : 'active';
                $z['responsable'] = 'Non assigné';
                $z['infrastructures_count'] = 0;
                $z['show_url'] = '#';
                return $z;
            }, PlaceholderData::mapZones());

        // Infrastructures réelles géolocalisées
        $realInfrastructures = Infrastructure::with(['zone'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (Infrastructure $infra) use ($routePrefix) {
                $sante = $infra->sante;
                return [
                    'id' => (string) $infra->id,
                    'nom' => $infra->nom,
                    'name' => $infra->nom,
                    'reference' => $infra->reference,
                    'type_key' => $infra->type->value,
                    'type_label' => $infra->type->label(),
                    'etat_key' => $infra->etat->value,
                    'etat_label' => $infra->etat->label(),
                    'etat_color' => $infra->etat->color(),
                    'criticite_key' => $infra->niveau_criticite?->value ?? 'moyen',
                    'criticite_label' => $infra->niveau_criticite?->label() ?? 'Moyen',
                    'criticite_color' => $infra->niveau_criticite?->color() ?? 'amber',
                    'sante_score' => $sante['score'] ?? 100,
                    'sante_label' => $sante['label'] ?? 'Bon',
                    'sante_color' => $sante['color'] ?? 'green',
                    'zone_id' => (string) $infra->zone_id,
                    'zone_nom' => $infra->zone?->nom ?? 'Zone non définie',
                    'lat' => (float) $infra->latitude,
                    'lng' => (float) $infra->longitude,
                    'capacite' => $infra->capacite ? ($infra->capacite . ' ' . ($infra->unite_capacite ?? '')) : null,
                    'date_installation' => $infra->date_installation?->format('d/m/Y'),
                    'date_derniere_maintenance' => $infra->date_derniere_maintenance?->format('d/m/Y') ?? 'Aucune',
                    'date_prochaine_maintenance' => $infra->date_prochaine_maintenance?->format('d/m/Y') ?? 'Non planifiée',
                    'is_maintenance_retard' => (bool) ($infra->date_prochaine_maintenance && $infra->date_prochaine_maintenance->isPast()),
                    'show_url' => route($routePrefix . '.infrastructures.show', $infra),
                ];
            })->values()->toArray();

        $pipelines = PlaceholderData::mapPipelines();
        $stats = PlaceholderData::stats();

        $measurementPoints = PointMesure::with('derniereMesure')
            ->where('statut', 'actif')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (PointMesure $point) use ($waterQualityService): array {
                $measurement = $point->derniereMesure;
                $assessment = $measurement
                    ? $waterQualityService->assess($measurement->only([
                        'ph',
                        'turbidite',
                        'chlore_residuel',
                        'plomb',
                        'nitrates',
                    ]))
                    : null;

                return [
                    'id' => $point->id,
                    'name' => $point->nom,
                    'code' => $point->code,
                    'zone' => $point->zone,
                    'address' => $point->adresse,
                    'lat' => (float) $point->latitude,
                    'lng' => (float) $point->longitude,
                    'type' => $point->type,
                    'status' => match ($assessment['status'] ?? null) {
                        'Non-Compliant' => 'critical',
                        'Alert' => 'alert',
                        default => 'normal',
                    },
                    'measurement_status' => $assessment['status'] ?? 'No measurement',
                    'overall_compliance' => $assessment['overall_compliance'] ?? null,
                    'measurement' => $measurement ? [
                        'reference' => $measurement->reference,
                        'date' => $measurement->date_mesure->format('Y-m-d H:i'),
                        'ph' => (float) $measurement->ph,
                        'turbidite' => (float) $measurement->turbidite,
                        'chlore_residuel' => (float) $measurement->chlore_residuel,
                        'plomb' => (float) $measurement->plomb,
                        'nitrates' => (float) $measurement->nitrates,
                        'is_verified' => $measurement->is_verified,
                        'verifier' => $measurement->verifier,
                    ] : null,
                ];
            });

        $infrastructureTypes = InfrastructureType::cases();
        $infrastructureEtats = InfrastructureEtat::cases();

        $focusZone = $request->query('zone');
        $focusInfrastructure = $request->query('infrastructure');

        return view('manager.map', [
            'layout' => $isAdmin ? 'layouts.admin' : 'layouts.manager',
            'contentSection' => $isAdmin ? 'admin-content' : 'manager-content',
            'dashboardRoute' => $isAdmin ? 'admin.dashboard' : 'manager.dashboard',
            'zones' => $zonesData,
            'infrastructures' => $realInfrastructures,
            'pipelines' => $pipelines,
            'stats' => $stats,
            'user' => $user,
            'measurementPoints' => $measurementPoints,
            'infrastructureTypes' => $infrastructureTypes,
            'infrastructureEtats' => $infrastructureEtats,
            'focusZone' => $focusZone,
            'focusInfrastructure' => $focusInfrastructure,
        ]);
    }
}
