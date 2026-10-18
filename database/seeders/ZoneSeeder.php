<?php

namespace Database\Seeders;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Enums\ZoneStatut;
use App\Models\Infrastructure;
use App\Models\InfrastructureHistorique;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Crée 6 zones du réseau tunisien, chacune avec 3 à 6 infrastructures géolocalisées et leur historique.
     */
    public function run(): void
    {
        if (Zone::exists()) {
            return;
        }

        $responsables = User::all();

        $zonesData = [
            [
                'nom' => 'Grand Tunis',
                'localisation' => 'Tunis',
                'description' => "Réseau principal de la capitale et de sa banlieue. Adduction depuis le complexe de Ghdir El Golla et les barrages du nord-ouest.",
                'latitude' => 36.8065,
                'longitude' => 10.1815,
                'superficie_km2' => 346.50,
                'population_desservie' => 1056000,
                'statut' => ZoneStatut::Active,
                'code' => 'TUN',
            ],
            [
                'nom' => 'Cap Bon',
                'localisation' => 'Nabeul',
                'description' => "Zone côtière à forte demande estivale et agricole. Réseau soumis à des variations saisonnières de pression.",
                'latitude' => 36.4561,
                'longitude' => 10.7376,
                'superficie_km2' => 480.00,
                'population_desservie' => 380000,
                'statut' => ZoneStatut::Active,
                'code' => 'NAB',
            ],
            [
                'nom' => 'Sahel',
                'localisation' => 'Sousse',
                'description' => "Secteur urbain et touristique du littoral du Sahel. Alimenté principalement par le barrage de Nebhana et l'adduction du nord.",
                'latitude' => 35.8256,
                'longitude' => 10.6411,
                'superficie_km2' => 266.00,
                'population_desservie' => 675000,
                'statut' => ZoneStatut::Active,
                'code' => 'SOU',
            ],
            [
                'nom' => 'Sfax Agglomération',
                'localisation' => 'Sfax',
                'description' => "Bassin industriel et résidentiel majeur du sud-est. Réseau interconnecté avec la nouvelle station de dessalement d'eau de mer.",
                'latitude' => 34.7406,
                'longitude' => 10.7603,
                'superficie_km2' => 520.00,
                'population_desservie' => 955000,
                'statut' => ZoneStatut::Active,
                'code' => 'SFX',
            ],
            [
                'nom' => 'Djerba & Zarzis',
                'localisation' => 'Djerba',
                'description' => "Périmètre insulaire alimenté par l'usine de dessalement d'eau de mer de Mezraya et des forages profonds.",
                'latitude' => 33.8076,
                'longitude' => 10.8572,
                'superficie_km2' => 514.00,
                'population_desservie' => 164000,
                'statut' => ZoneStatut::Active,
                'code' => 'DJE',
            ],
            [
                'nom' => 'Bizerte Nord',
                'localisation' => 'Bizerte',
                'description' => "Secteur nord alimenté par les retenues des barrages de Sidi Salem et Joumine. Réseau à topographie accidentée.",
                'latitude' => 37.2744,
                'longitude' => 9.8739,
                'superficie_km2' => 395.00,
                'population_desservie' => 568000,
                'statut' => ZoneStatut::Active,
                'code' => 'BIZ',
            ],
        ];

        $infraCounter = 1;

        foreach ($zonesData as $data) {
            $code = $data['code'];
            unset($data['code']);

            $data['responsable_id'] = $responsables->isNotEmpty()
                ? $responsables->random()->id
                : null;

            $zone = Zone::create($data);

            $nbInfras = fake()->numberBetween(3, 6);

            for ($i = 1; $i <= $nbInfras; $i++) {
                $type = fake()->randomElement(InfrastructureType::cases());
                $etatFinal = fake()->randomElement([
                    InfrastructureEtat::EnService,
                    InfrastructureEtat::EnService,
                    InfrastructureEtat::EnService,
                    InfrastructureEtat::EnMaintenance,
                    InfrastructureEtat::HorsService,
                ]);

                // Coordonnées avec léger décalage aléatoire (+/- 0.02) autour du centre de la zone
                $latOffset = fake()->randomFloat(7, -0.02, 0.02);
                $lngOffset = fake()->randomFloat(7, -0.02, 0.02);

                $dateInstall = fake()->dateTimeBetween('-20 years', '-1 year');
                $dateDerniere = fake()->dateTimeBetween($dateInstall, '-1 month');

                // Pour tester les alertes de retard, certaines dates de prochaine maintenance sont dans le passé
                $isEnRetard = fake()->boolean(30);
                $dateProchaine = $isEnRetard
                    ? fake()->dateTimeBetween('-45 days', '-2 days')
                    : fake()->dateTimeBetween('+15 days', '+8 months');

                $unite = match ($type) {
                    InfrastructureType::Reservoir => 'm3',
                    InfrastructureType::StationPompage => 'm3/h',
                    InfrastructureType::StationTraitement => 'm3/h',
                    InfrastructureType::Forage => 'l/s',
                    InfrastructureType::Canalisation => 'm3/h',
                    InfrastructureType::Vanne => 'l/s',
                };

                $capacite = match ($type) {
                    InfrastructureType::Reservoir => fake()->randomFloat(2, 1000, 25000),
                    InfrastructureType::StationPompage => fake()->randomFloat(2, 250, 4500),
                    InfrastructureType::StationTraitement => fake()->randomFloat(2, 500, 8000),
                    InfrastructureType::Forage => fake()->randomFloat(2, 15, 120),
                    InfrastructureType::Canalisation => fake()->randomFloat(2, 100, 2000),
                    InfrastructureType::Vanne => fake()->randomFloat(2, 50, 600),
                };

                $nom = $type->label().' '.$zone->localisation.'-'.sprintf('%02d', $i);
                $reference = sprintf('INF-%s-%03d', $code, $infraCounter++);

                $infrastructure = Infrastructure::create([
                    'nom' => $nom,
                    'type' => $type,
                    'localisation' => fake('fr_FR')->streetAddress().', '.$zone->localisation,
                    'etat' => $etatFinal,
                    'date_installation' => $dateInstall,
                    'zone_id' => $zone->id,
                    'latitude' => round($zone->latitude + $latOffset, 7),
                    'longitude' => round($zone->longitude + $lngOffset, 7),
                    'capacite' => $capacite,
                    'unite_capacite' => $unite,
                    'fabricant' => fake()->randomElement([
                        'SONEDE Technologies',
                        'Grundfos Pompes',
                        'Veolia Maghreb',
                        'Schneider Water Systems',
                        'KSB Tunisie',
                        'Flowserve Méditerranée',
                    ]),
                    'reference' => $reference,
                    'date_derniere_maintenance' => $dateDerniere,
                    'date_prochaine_maintenance' => $dateProchaine,
                    'duree_vie_annees' => fake()->randomElement([15, 20, 25, 30, 40]),
                    'niveau_criticite' => fake()->randomElement(NiveauCriticite::cases()),
                ]);

                // Supprimer l'historique généré automatiquement par l'observer pour forger un historique complet
                $infrastructure->historiques()->delete();

                // Génération de 1 à 4 entrées d'historique réalistes antichronologiques
                $nbHistoriques = fake()->numberBetween(1, 4);
                $this->genererHistoriqueRealiste($infrastructure, $nbHistoriques, $etatFinal);
            }
        }
    }

    /**
     * Génère entre 1 et 4 étapes d'historique cohérentes menant à l'état final actuel.
     */
    private function genererHistoriqueRealiste(Infrastructure $infra, int $nbEtapes, InfrastructureEtat $etatFinal): void
    {
        $auteurs = ['Ingénieur SONEDE', 'Chef de Centre', 'Technicien Réseau', 'Système AquaSecure', 'Superviseur SCADA'];

        // Étape 1 : Création initiale à la date d'installation
        $historiques = [
            [
                'ancien_etat' => null,
                'nouvel_etat' => InfrastructureEtat::EnService->value,
                'commentaire' => 'Mise en service initiale après réception et essais d’étanchéité.',
                'user_name' => 'Ingénieur Réception SONEDE',
                'created_at' => $infra->date_installation->copy()->startOfDay(),
            ],
        ];

        if ($nbEtapes === 1) {
            // Si une seule étape mais l'état n'est pas EnService, adapter
            $historiques[0]['nouvel_etat'] = $etatFinal->value;
        } elseif ($nbEtapes === 2) {
            $historiques[] = [
                'ancien_etat' => InfrastructureEtat::EnService->value,
                'nouvel_etat' => $etatFinal->value,
                'commentaire' => $this->commentairePourTransition(InfrastructureEtat::EnService, $etatFinal),
                'user_name' => fake()->randomElement($auteurs),
                'created_at' => now()->subMonths(fake()->numberBetween(1, 6)),
            ];
        } elseif ($nbEtapes === 3) {
            $historiques[] = [
                'ancien_etat' => InfrastructureEtat::EnService->value,
                'nouvel_etat' => InfrastructureEtat::EnMaintenance->value,
                'commentaire' => 'Arrêt planifié pour contrôle annuel des vannes et mesures vibratoires.',
                'user_name' => fake()->randomElement($auteurs),
                'created_at' => now()->subMonths(fake()->numberBetween(7, 12)),
            ];
            $historiques[] = [
                'ancien_etat' => InfrastructureEtat::EnMaintenance->value,
                'nouvel_etat' => $etatFinal->value,
                'commentaire' => $this->commentairePourTransition(InfrastructureEtat::EnMaintenance, $etatFinal),
                'user_name' => fake()->randomElement($auteurs),
                'created_at' => now()->subMonths(fake()->numberBetween(1, 5)),
            ];
        } else { // 4 étapes
            $historiques[] = [
                'ancien_etat' => InfrastructureEtat::EnService->value,
                'nouvel_etat' => InfrastructureEtat::EnMaintenance->value,
                'commentaire' => 'Inspection de routine et remplacement préventif des garnitures mécaniques.',
                'user_name' => fake()->randomElement($auteurs),
                'created_at' => now()->subMonths(fake()->numberBetween(14, 20)),
            ];
            $historiques[] = [
                'ancien_etat' => InfrastructureEtat::EnMaintenance->value,
                'nouvel_etat' => InfrastructureEtat::EnService->value,
                'commentaire' => 'Essais de mise en pression réussis, reprise du régime normal.',
                'user_name' => fake()->randomElement($auteurs),
                'created_at' => now()->subMonths(fake()->numberBetween(8, 13)),
            ];
            $historiques[] = [
                'ancien_etat' => InfrastructureEtat::EnService->value,
                'nouvel_etat' => $etatFinal->value,
                'commentaire' => $this->commentairePourTransition(InfrastructureEtat::EnService, $etatFinal),
                'user_name' => fake()->randomElement($auteurs),
                'created_at' => now()->subMonths(fake()->numberBetween(1, 4)),
            ];
        }

        foreach ($historiques as $h) {
            InfrastructureHistorique::create([
                'infrastructure_id' => $infra->id,
                'ancien_etat' => $h['ancien_etat'],
                'nouvel_etat' => $h['nouvel_etat'],
                'commentaire' => $h['commentaire'],
                'user_name' => $h['user_name'],
                'created_at' => $h['created_at'],
            ]);
        }
    }

    private function commentairePourTransition(InfrastructureEtat $ancien, InfrastructureEtat $nouveau): string
    {
        return match ($nouveau) {
            InfrastructureEtat::EnService => 'Remise en exploitation après validation technique et vérification de conformité.',
            InfrastructureEtat::EnMaintenance => 'Passage en maintenance suite à détection d’anomalie de pression ou opération programmée.',
            InfrastructureEtat::HorsService => 'Mise hors service d’urgence suite à alerte critique de fuite ou surpression.',
        };
    }
}
