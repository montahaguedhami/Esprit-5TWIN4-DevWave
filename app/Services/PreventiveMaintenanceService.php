<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PreventiveMaintenanceService
{
    public const PLANIFIEE = 'planifiee';

    public const DEJA_PLANIFIEE = 'deja_planifiee';

    public const AUCUN_TECHNICIEN = 'aucun_technicien';

    public function __construct(private InterventionPlanningService $planning)
    {
    }

    /**
     * Crée les interventions préventives des infrastructures dont la maintenance arrive à échéance.
     *
     * @return Collection<int, array{infrastructure: Infrastructure, echeance: Carbon, resultat: string, date: ?Carbon, technicien: ?Technicien, hors_specialite: bool, intervention: ?Intervention}>
     */
    public function planifier(?int $horizonJours = null, bool $simulation = false): Collection
    {
        $horizonJours ??= (int) config('maintenance.horizon_jours', 7);

        // Affectations décidées pendant ce passage (utile en simulation, où rien n'est écrit en base)
        $affectations = [];

        return Infrastructure::query()
            ->with('zone')
            ->whereNotNull('date_prochaine_maintenance')
            ->whereDate('date_prochaine_maintenance', '<=', today()->addDays($horizonJours)->toDateString())
            ->orderBy('date_prochaine_maintenance')
            ->get()
            ->map(function (Infrastructure $infrastructure) use (&$affectations, $simulation) {
                $ligne = [
                    'infrastructure' => $infrastructure,
                    'echeance' => $infrastructure->date_prochaine_maintenance,
                    'resultat' => self::AUCUN_TECHNICIEN,
                    'date' => null,
                    'technicien' => null,
                    'hors_specialite' => false,
                    'intervention' => null,
                ];

                // Une intervention active existe déjà : rien à créer
                $existante = $infrastructure->interventions()
                    ->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
                    ->exists();
                if ($existante) {
                    return ['resultat' => self::DEJA_PLANIFIEE] + $ligne;
                }

                // Échéance dépassée : on planifie au plus tôt (aujourd'hui)
                $debut = $infrastructure->date_prochaine_maintenance->copy()->max(today());

                for ($decalage = 0; $decalage <= (int) config('maintenance.recherche_jours', 7); $decalage++) {
                    $jour = $debut->copy()->addDays($decalage);
                    $choix = $this->choisirTechnicien($infrastructure, $jour, $affectations);

                    if ($choix) {
                        [$technicien, $horsSpecialite] = $choix;
                        $affectations[] = $technicien->id.'|'.$jour->toDateString();

                        $intervention = $simulation ? null : Intervention::create([
                            'technicien_id' => $technicien->id,
                            'infrastructure_id' => $infrastructure->id,
                            'date' => $jour->toDateString(),
                            'description' => $this->description($infrastructure),
                            'statut' => 'Planifiée',
                            'type' => Intervention::TYPE_PREVENTIVE,
                            'cout' => $this->estimerCout($infrastructure),
                        ]);

                        return [
                            'resultat' => self::PLANIFIEE,
                            'date' => $jour,
                            'technicien' => $technicien,
                            'hors_specialite' => $horsSpecialite,
                            'intervention' => $intervention,
                        ] + $ligne;
                    }
                }

                return $ligne;
            });
    }

    /**
     * Technicien le plus adapté pour ce jour : disponible, sans conflit, bonne spécialité, charge la plus faible.
     *
     * @param  list<string>  $affectations  paires « technicien|date » déjà prises pendant ce passage
     * @return array{0: Technicien, 1: bool}|null  [technicien, hors spécialité]
     */
    public function choisirTechnicien(Infrastructure $infrastructure, Carbon $jour, array $affectations = []): ?array
    {
        $specialite = config('maintenance.specialite_par_type.'.$infrastructure->type?->value);
        $fenetre = (int) config('maintenance.fenetre_charge_jours', 30);

        $candidat = Technicien::query()
            ->where('disponibilite', '!=', 'En congé')
            ->withCount(['interventions as charge' => fn ($query) => $query
                ->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
                ->whereDate('date', '>=', today()->toDateString())
                ->whereDate('date', '<=', today()->addDays($fenetre)->toDateString())])
            ->get()
            ->reject(fn (Technicien $technicien) => in_array($technicien->id.'|'.$jour->toDateString(), $affectations, true)
                || $this->planning->conflitPour($technicien->id, $jour))
            ->sortBy([
                // 1. bonne spécialité d'abord, 2. charge la plus faible (y compris ce passage), 3. ordre alphabétique
                fn (Technicien $a, Technicien $b) => ($b->specialite === $specialite) <=> ($a->specialite === $specialite),
                fn (Technicien $a, Technicien $b) => $this->chargeTotale($a, $affectations) <=> $this->chargeTotale($b, $affectations),
                fn (Technicien $a, Technicien $b) => strcmp($a->nom, $b->nom),
            ])
            ->first();

        return $candidat ? [$candidat, $specialite !== null && $candidat->specialite !== $specialite] : null;
    }

    /**
     * Prochaine échéance : périodicité du type, raccourcie pour les infrastructures critiques.
     */
    public function prochaineEcheance(Infrastructure $infrastructure, Carbon $depuis): Carbon
    {
        $mois = config('maintenance.periodicite_mois.'.$infrastructure->type?->value)
            ?? config('maintenance.periodicite_defaut_mois', 6);
        $facteur = config('maintenance.facteur_criticite.'.$infrastructure->niveau_criticite?->value, 1.0);

        return $depuis->copy()->addMonthsNoOverflow(max(1, (int) round($mois * $facteur)));
    }

    /**
     * Coût estimé : moyenne des interventions terminées sur cette infrastructure.
     */
    private function estimerCout(Infrastructure $infrastructure): float
    {
        return round((float) $infrastructure->interventions()->where('statut', 'Terminée')->avg('cout'), 2);
    }

    private function description(Infrastructure $infrastructure): string
    {
        return sprintf(
            'Maintenance préventive programmée — %s « %s »%s (échéance du %s).',
            $infrastructure->type?->value ?? 'Infrastructure',
            $infrastructure->nom,
            $infrastructure->zone ? ', zone '.$infrastructure->zone->nom : '',
            $infrastructure->date_prochaine_maintenance->format('d/m/Y')
        );
    }

    /**
     * @param  list<string>  $affectations
     */
    private function chargeTotale(Technicien $technicien, array $affectations): int
    {
        $ajoutees = count(array_filter($affectations, fn (string $cle) => str_starts_with($cle, $technicien->id.'|')));

        return $technicien->charge + $ajoutees;
    }
}
