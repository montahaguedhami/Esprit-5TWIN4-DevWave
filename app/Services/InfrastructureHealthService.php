<?php

namespace App\Services;

use App\Enums\InfrastructureEtat;
use App\Models\Infrastructure;
use Carbon\CarbonInterface;

class InfrastructureHealthService
{
    /**
     * Calcule le score de santé de l'infrastructure et détaille les facteurs explicatifs.
     *
     * @return array{score: int, niveau: string, facteurs: list<string>}
     */
    public function score(Infrastructure $infrastructure): array
    {
        $config = config('infrastructure.sante', [
            'poids_etat' => 0.40,
            'poids_age' => 0.25,
            'poids_retard_maintenance' => 0.20,
            'poids_interventions' => 0.15,
        ]);

        $hasInterventionsRelation = method_exists($infrastructure, 'interventions');

        $poidsEtat = (float) ($config['poids_etat'] ?? 0.40);
        $poidsAge = (float) ($config['poids_age'] ?? 0.25);
        $poidsRetard = (float) ($config['poids_retard_maintenance'] ?? 0.20);
        $poidsInterventions = $hasInterventionsRelation ? (float) ($config['poids_interventions'] ?? 0.15) : 0.0;

        $totalPoids = $poidsEtat + $poidsAge + $poidsRetard + $poidsInterventions;
        if ($totalPoids <= 0) {
            $totalPoids = 1.0;
        }

        $facteurs = [];

        // 1. Facteur État
        $scoreEtat = match ($infrastructure->etat) {
            InfrastructureEtat::EnService => 100,
            InfrastructureEtat::EnMaintenance => 50,
            InfrastructureEtat::HorsService => 0,
            default => 50,
        };
        $facteurs[] = match ($infrastructure->etat) {
            InfrastructureEtat::EnService => 'Équipement en service opérationnel (100/100)',
            InfrastructureEtat::EnMaintenance => 'Équipement actuellement en maintenance (50/100)',
            InfrastructureEtat::HorsService => 'Équipement hors service (0/100)',
            default => 'État non standard',
        };

        // 2. Facteur Âge vs Durée de vie
        $scoreAge = 90;
        if ($infrastructure->duree_vie_annees && $infrastructure->date_installation) {
            $ageAnnees = (int) $infrastructure->date_installation->diffInYears(now());
            $dureeVie = (int) $infrastructure->duree_vie_annees;

            if ($dureeVie > 0) {
                if ($ageAnnees >= $dureeVie) {
                    $scoreAge = 10;
                    $depassement = $ageAnnees - $dureeVie;
                    $facteurs[] = "Durée de vie théorique dépassée de {$depassement} an(s) ({$ageAnnees}/{$dureeVie} ans)";
                } else {
                    $pourcentageConsomme = round(($ageAnnees / $dureeVie) * 100);
                    $scoreAge = (int) max(15, 100 - $pourcentageConsomme);
                    $facteurs[] = "Âge : {$ageAnnees} an(s) sur {$dureeVie} ans prévus ({$pourcentageConsomme} % de vie consommé)";
                }
            } else {
                $facteurs[] = 'Durée de vie théorique non spécifiée';
            }
        } else {
            $facteurs[] = 'Durée de vie théorique non spécifiée (estimée conforme)';
        }

        // 3. Facteur Retard de maintenance
        $scoreRetard = 85;
        if ($infrastructure->date_prochaine_maintenance) {
            $aujourdhui = now()->startOfDay();
            $dateProchaine = $infrastructure->date_prochaine_maintenance->copy()->startOfDay();

            if ($dateProchaine->isPast()) {
                $retardJours = (int) $dateProchaine->diffInDays($aujourdhui);
                $perte = min(100, $retardJours * 2);
                $scoreRetard = (int) max(0, 100 - $perte);
                $facteurs[] = "Maintenance en retard de {$retardJours} jour(s)";
            } else {
                $scoreRetard = 100;
                $facteurs[] = 'Maintenance à jour (prochaine intervention prévue le ' . $infrastructure->date_prochaine_maintenance->format('d/m/Y') . ')';
            }
        } else {
            $facteurs[] = 'Aucune date de maintenance planifiée';
        }

        // 4. Facteur Interventions (si relation existante)
        $scoreInterventions = 100;
        if ($hasInterventionsRelation) {
            if (isset($infrastructure->interventions_12_mois_count)) {
                $nbInterventions12Mois = (int) $infrastructure->interventions_12_mois_count;
            } elseif ($infrastructure->relationLoaded('interventions')) {
                $nbInterventions12Mois = (int) $infrastructure->interventions
                    ->filter(fn ($i) => $i->date && $i->date->gte(now()->subYear()))
                    ->count();
            } elseif ($infrastructure->exists) {
                $nbInterventions12Mois = (int) $infrastructure->interventions()
                    ->where('date', '>=', now()->subYear())
                    ->count();
            } else {
                $nbInterventions12Mois = 0;
            }

            if ($nbInterventions12Mois > 5) {
                $scoreInterventions = max(20, 100 - ($nbInterventions12Mois * 12));
                $facteurs[] = "Fréquence d'interventions élevée ({$nbInterventions12Mois} sur les 12 derniers mois)";
            } else {
                $facteurs[] = "Historique d'interventions stable ({$nbInterventions12Mois} sur 12 mois)";
            }
        }

        // Calcul pondéré
        $scorePondere = (
            ($scoreEtat * $poidsEtat) +
            ($scoreAge * $poidsAge) +
            ($scoreRetard * $poidsRetard) +
            ($scoreInterventions * $poidsInterventions)
        ) / $totalPoids;

        $scoreFinal = (int) min(100, max(0, round($scorePondere)));

        $niveau = match (true) {
            $scoreFinal >= 75 => 'bon',
            $scoreFinal >= 45 => 'surveiller',
            default => 'critique',
        };

        $label = match ($niveau) {
            'bon' => 'Bon',
            'surveiller' => 'À surveiller',
            default => 'Critique',
        };

        $color = match ($niveau) {
            'bon' => 'green',
            'surveiller' => 'amber',
            default => 'red',
        };

        return [
            'score' => $scoreFinal,
            'niveau' => $niveau,
            'label' => $label,
            'color' => $color,
            'facteurs' => $facteurs,
        ];
    }

    public function calculer(Infrastructure $infrastructure): array
    {
        return $this->score($infrastructure);
    }
}
