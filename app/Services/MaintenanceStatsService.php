<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Indicateurs du module Maintenance (tableau de bord et rapport mensuel).
 * Les agrégations sont faites en PHP pour rester identiques sous MySQL et SQLite.
 */
class MaintenanceStatsService
{
    /**
     * Indicateurs clés sur les 12 derniers mois.
     *
     * @return array<string, int|float|null>
     */
    public function indicateurs(): array
    {
        $depuis = today()->subMonths(12);
        $interventions = Intervention::query()->whereDate('date', '>=', $depuis->toDateString())->get();

        $terminees = $interventions->where('statut', 'Terminée');
        // Interventions échues (date passée, non annulées) : base du taux de réalisation
        $echues = $interventions->filter(fn (Intervention $i) => $i->date->lte(today()) && $i->statut !== 'Annulée');

        return [
            'ce_mois' => $interventions->filter(fn (Intervention $i) => $i->date->isSameMonth(today()))->count(),
            'en_retard' => $this->enRetard()->count(),
            'a_venir' => Intervention::query()
                ->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
                ->whereDate('date', '>=', today()->toDateString())
                ->count(),
            'taux_realisation' => $echues->isEmpty() ? null : round($terminees->count() / $echues->count() * 100),
            'cout_total' => round((float) $terminees->sum('cout'), 2),
            'cout_moyen' => $terminees->isEmpty() ? null : round((float) $terminees->avg('cout'), 2),
            'part_preventive' => $interventions->isEmpty()
                ? null
                : round($interventions->where('type', Intervention::TYPE_PREVENTIVE)->count() / $interventions->count() * 100),
        ];
    }

    /**
     * Coûts des interventions terminées par mois, préventif / correctif (12 derniers mois).
     *
     * @return array{labels: list<string>, preventive: list<float>, corrective: list<float>}
     */
    public function coutsParMois(int $mois = 12): array
    {
        $debut = today()->startOfMonth()->subMonths($mois - 1);
        $terminees = Intervention::query()
            ->where('statut', 'Terminée')
            ->whereDate('date', '>=', $debut->toDateString())
            ->get();

        $resultat = ['labels' => [], 'preventive' => [], 'corrective' => []];

        for ($m = $debut->copy(); $m->lte(today()); $m->addMonth()) {
            $duMois = $terminees->filter(fn (Intervention $i) => $i->date->isSameMonth($m));
            $resultat['labels'][] = ucfirst($m->locale('fr')->translatedFormat('M Y'));
            $resultat['preventive'][] = round((float) $duMois->where('type', Intervention::TYPE_PREVENTIVE)->sum('cout'), 2);
            $resultat['corrective'][] = round((float) $duMois->where('type', '!=', Intervention::TYPE_PREVENTIVE)->sum('cout'), 2);
        }

        return $resultat;
    }

    /**
     * Nombre d'interventions par statut (12 derniers mois et à venir).
     *
     * @return array<string, int>
     */
    public function parStatut(): array
    {
        $comptes = Intervention::query()
            ->whereDate('date', '>=', today()->subMonths(12)->toDateString())
            ->get()
            ->countBy('statut');

        return collect(Intervention::STATUTS)->mapWithKeys(fn (string $s) => [$s => $comptes[$s] ?? 0])->all();
    }

    /**
     * Charge et performance de chaque technicien.
     *
     * @return Collection<int, array{technicien: Technicien, a_venir: int, terminees_30j: int, en_retard: int, cout_12m: float}>
     */
    public function chargeTechniciens(): Collection
    {
        $interventions = Intervention::query()
            ->whereDate('date', '>=', today()->subMonths(12)->toDateString())
            ->get()
            ->groupBy('technicien_id');

        return Technicien::query()->orderBy('nom')->get()
            ->map(function (Technicien $technicien) use ($interventions) {
                $siennes = $interventions->get($technicien->id, collect());
                $actives = $siennes->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS);

                return [
                    'technicien' => $technicien,
                    'a_venir' => $actives->filter(fn (Intervention $i) => $i->date->gte(today()))->count(),
                    'en_retard' => $actives->filter(fn (Intervention $i) => $i->date->lt(today()))->count(),
                    'terminees_30j' => $siennes->where('statut', 'Terminée')
                        ->filter(fn (Intervention $i) => $i->date->gte(today()->subDays(30)))->count(),
                    'cout_12m' => round((float) $siennes->where('statut', 'Terminée')->sum('cout'), 2),
                ];
            })
            ->sortByDesc('a_venir')
            ->values();
    }

    /**
     * Infrastructures qui tombent le plus souvent en panne (interventions correctives sur 12 mois).
     *
     * @return Collection<int, array{infrastructure: \App\Models\Infrastructure, pannes: int, cout: float}>
     */
    public function infrastructuresAPannes(int $limite = 5): Collection
    {
        return Intervention::query()
            ->with('infrastructure.zone')
            ->whereNotNull('infrastructure_id')
            ->where('type', '!=', Intervention::TYPE_PREVENTIVE)
            ->where('statut', '!=', 'Annulée')
            ->whereDate('date', '>=', today()->subMonths(12)->toDateString())
            ->get()
            ->filter(fn (Intervention $i) => $i->infrastructure !== null)
            ->groupBy('infrastructure_id')
            ->map(fn (Collection $groupe) => [
                'infrastructure' => $groupe->first()->infrastructure,
                'pannes' => $groupe->count(),
                'cout' => round((float) $groupe->sum('cout'), 2),
            ])
            ->sortByDesc('pannes')
            ->take($limite)
            ->values();
    }

    /**
     * Interventions actives dont la date est dépassée.
     *
     * @return Collection<int, Intervention>
     */
    public function enRetard(): Collection
    {
        return Intervention::query()
            ->with(['technicien', 'infrastructure'])
            ->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
            ->whereDate('date', '<', today()->toDateString())
            ->orderBy('date')
            ->get();
    }

    /**
     * Données du rapport mensuel.
     *
     * @return array<string, mixed>
     */
    public function rapportMensuel(Carbon $mois): array
    {
        $interventions = Intervention::query()
            ->with(['technicien', 'infrastructure.zone'])
            ->whereDate('date', '>=', $mois->copy()->startOfMonth()->toDateString())
            ->whereDate('date', '<=', $mois->copy()->endOfMonth()->toDateString())
            ->orderBy('date')
            ->get();

        $terminees = $interventions->where('statut', 'Terminée');

        return [
            'mois' => $mois,
            'interventions' => $interventions,
            'par_statut' => collect(Intervention::STATUTS)->mapWithKeys(fn (string $s) => [$s => $interventions->where('statut', $s)->count()])->all(),
            'par_type' => collect(Intervention::TYPES)->mapWithKeys(fn (string $t) => [$t => $interventions->where('type', $t)->count()])->all(),
            'cout_total' => round((float) $terminees->sum('cout'), 2),
            'par_technicien' => $interventions
                ->groupBy(fn (Intervention $i) => $i->technicien?->nom ?? '—')
                ->map(fn (Collection $groupe) => [
                    'total' => $groupe->count(),
                    'terminees' => $groupe->where('statut', 'Terminée')->count(),
                    'cout' => round((float) $groupe->where('statut', 'Terminée')->sum('cout'), 2),
                ])
                ->sortKeys(),
        ];
    }
}
