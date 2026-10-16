<?php

namespace App\Services;

use App\Models\Intervention;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InterventionPlanningService
{
    /**
     * Statuts qui occupent la journée d'un technicien.
     */
    public const STATUTS_ACTIFS = ['Planifiée', 'En cours'];

    /**
     * Retourne le message d'erreur si la planification est impossible, null sinon.
     */
    public function verifier(int $technicienId, string $date, string $statut, ?int $ignorerId = null): ?string
    {
        if (! in_array($statut, self::STATUTS_ACTIFS, true)) {
            return null;
        }

        $technicien = Technicien::find($technicienId);
        if (! $technicien) {
            return null;
        }

        $jour = Carbon::parse($date)->startOfDay();

        // 1. Technicien en congé : aucune nouvelle intervention active à partir d'aujourd'hui
        if ($technicien->disponibilite === 'En congé' && $jour->gte(today())) {
            return "{$technicien->nom} est en congé : impossible de lui planifier une intervention.";
        }

        // 2. Conflit : le technicien a déjà une intervention active ce jour-là
        $conflit = $this->conflitPour($technicienId, $jour, $ignorerId);
        if ($conflit) {
            return sprintf(
                '%s a déjà une intervention le %s (#%d : %s).',
                $technicien->nom,
                $jour->format('d/m/Y'),
                $conflit->id,
                str($conflit->description)->limit(40)
            );
        }

        return null;
    }

    /**
     * Intervention active du même technicien le même jour (hors intervention en cours d'édition).
     */
    public function conflitPour(int $technicienId, Carbon $jour, ?int $ignorerId = null): ?Intervention
    {
        return Intervention::query()
            ->where('technicien_id', $technicienId)
            ->whereDate('date', $jour->toDateString())
            ->whereIn('statut', self::STATUTS_ACTIFS)
            ->when($ignorerId, fn ($query) => $query->whereKeyNot($ignorerId))
            ->first();
    }

    /**
     * Interventions actives à venir affectées à un technicien actuellement en congé (à réaffecter).
     *
     * @return Collection<int, Intervention>
     */
    public function affectationsEnConge(): Collection
    {
        return Intervention::query()
            ->with('technicien')
            ->whereIn('statut', self::STATUTS_ACTIFS)
            ->whereDate('date', '>=', today()->toDateString())
            ->whereHas('technicien', fn ($query) => $query->where('disponibilite', 'En congé'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Conflits déjà présents en base : technicien + jour avec au moins deux interventions actives.
     *
     * @return Collection<int, array{technicien: Technicien, date: Carbon, interventions: Collection<int, Intervention>}>
     */
    public function conflitsExistants(?Carbon $depuis = null): Collection
    {
        return Intervention::query()
            ->with('technicien')
            ->whereIn('statut', self::STATUTS_ACTIFS)
            ->when($depuis, fn ($query) => $query->whereDate('date', '>=', $depuis->toDateString()))
            ->orderBy('date')
            ->get()
            ->groupBy(fn (Intervention $intervention) => $intervention->technicien_id.'|'.$intervention->date->toDateString())
            ->filter(fn (Collection $groupe) => $groupe->count() > 1)
            ->map(fn (Collection $groupe) => [
                'technicien' => $groupe->first()->technicien,
                'date' => $groupe->first()->date,
                'interventions' => $groupe->values(),
            ])
            ->values();
    }
}
