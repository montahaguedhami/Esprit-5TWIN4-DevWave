<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\Zone;
use App\Services\ZoneStatsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventaireExportController extends Controller
{
    /**
     * Export streaming CSV pour les zones.
     */
    public function exportZonesCsv(): StreamedResponse
    {
        $filename = 'zones-inventaire-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour ouverture correcte dans Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // En-tête CSV (séparateur point-virgule)
            fputcsv($handle, [
                'ID',
                'Nom',
                'Localisation',
                'Statut',
                'Responsable',
                'Superficie (km²)',
                'Population desservie',
                'Latitude',
                'Longitude',
                'Nb Équipements',
                'Date de création',
            ], ';');

            Zone::with(['responsable'])
                ->withCount('infrastructures')
                ->orderBy('nom')
                ->chunk(100, function ($zones) use ($handle): void {
                    foreach ($zones as $zone) {
                        fputcsv($handle, [
                            $zone->id,
                            $zone->nom,
                            $zone->localisation,
                            $zone->statut?->label() ?? 'Active',
                            $zone->responsable?->name ?? 'Non assigné',
                            $zone->superficie_km2 ? (string) $zone->superficie_km2 : '',
                            $zone->population_desservie ? (string) $zone->population_desservie : '',
                            $zone->latitude ? (string) $zone->latitude : '',
                            $zone->longitude ? (string) $zone->longitude : '',
                            $zone->infrastructures_count,
                            $zone->created_at?->format('d/m/Y H:i'),
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export streaming CSV pour les infrastructures.
     */
    public function exportInfrastructuresCsv(): StreamedResponse
    {
        $filename = 'infrastructures-inventaire-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            // En-têtes CSV
            fputcsv($handle, [
                'ID',
                'Nom',
                'Référence',
                'Zone',
                'Type',
                'État',
                'Criticité',
                'Santé (Score)',
                'Santé (Libellé)',
                'Capacité',
                'Fabricant',
                'Date Installation',
                'Dernière Maintenance',
                'Prochaine Maintenance',
                'Latitude',
                'Longitude',
            ], ';');

            Infrastructure::with(['zone'])
                ->orderBy('nom')
                ->chunk(100, function ($infrastructures) use ($handle): void {
                    foreach ($infrastructures as $infra) {
                        $sante = $infra->sante;
                        fputcsv($handle, [
                            $infra->id,
                            $infra->nom,
                            $infra->reference ?? '',
                            $infra->zone?->nom ?? '',
                            $infra->type->label(),
                            $infra->etat->label(),
                            $infra->niveau_criticite?->label() ?? 'Moyen',
                            $sante['score'],
                            $sante['label'],
                            $infra->capacite ? ($infra->capacite . ' ' . ($infra->unite_capacite ?? '')) : '',
                            $infra->fabricant ?? '',
                            $infra->date_installation?->format('d/m/Y') ?? '',
                            $infra->date_derniere_maintenance?->format('d/m/Y') ?? '',
                            $infra->date_prochaine_maintenance?->format('d/m/Y') ?? '',
                            $infra->latitude ? (string) $infra->latitude : '',
                            $infra->longitude ? (string) $infra->longitude : '',
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Fiche technique PDF pour une Zone.
     */
    public function exportZonePdf(Zone $zone, ZoneStatsService $statsService): Response
    {
        $zone->load([
            'responsable',
            'infrastructures' => fn ($q) => $q->orderBy('nom'),
            'incidents' => fn ($q) => $q->latest('date_signalement')->limit(10),
            'projets',
        ]);

        $stats = $statsService->statsZone($zone);

        $pdf = Pdf::loadView('manager.exports.zone-pdf', [
            'zone' => $zone,
            'stats' => $stats,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->setPaper('a4', 'portrait');

        $slug = \Illuminate\Support\Str::slug($zone->nom);
        return $pdf->download("fiche-zone-{$zone->id}-{$slug}.pdf");
    }

    /**
     * Fiche technique PDF pour une Infrastructure.
     */
    public function exportInfrastructurePdf(Infrastructure $infrastructure): Response
    {
        $infrastructure->load([
            'zone.responsable',
            'historiques',
            'interventions.technicien',
            'incidents' => fn ($q) => $q->latest('date_signalement')->limit(10),
        ]);

        $pdf = Pdf::loadView('manager.exports.infrastructure-pdf', [
            'infrastructure' => $infrastructure,
            'sante' => $infrastructure->sante,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->setPaper('a4', 'portrait');

        $slug = \Illuminate\Support\Str::slug($infrastructure->nom);
        return $pdf->download("fiche-infrastructure-{$infrastructure->id}-{$slug}.pdf");
    }
}
