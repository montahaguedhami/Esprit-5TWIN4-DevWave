<?php

namespace App\Console\Commands;

use App\Models\Infrastructure;
use App\Models\InfrastructureAlerte;
use Illuminate\Console\Command;

class CheckMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infrastructures:check-maintenance {--days=7 : Nombre de jours pour anticiper la maintenance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les retards de maintenance et les durées de vie des infrastructures et enregistre les alertes.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 7;
        }

        $aujourdhui = now()->startOfDay();
        $seuilFutur = now()->addDays($days)->endOfDay();

        $infrastructures = Infrastructure::with('zone')->get();
        $tableRows = [];
        $alertesCreees = 0;

        foreach ($infrastructures as $infra) {
            // 1. Détection maintenance dépassée
            if ($infra->date_prochaine_maintenance && $infra->date_prochaine_maintenance->startOfDay()->isPast()) {
                $retard = (int) $infra->date_prochaine_maintenance->startOfDay()->diffInDays($aujourdhui);
                $type = 'maintenance_depassee';
                $message = "Maintenance en retard de {$retard} jour(s) (prévue le {$infra->date_prochaine_maintenance->format('d/m/Y')}).";

                $cree = $this->enregistrerAlerteSiAbsente($infra->id, $type, $message);
                if ($cree) {
                    $alertesCreees++;
                }

                $tableRows[] = [
                    $infra->id,
                    $infra->nom,
                    $infra->zone?->nom ?? '—',
                    'Retard maintenance',
                    $message,
                ];
            }
            // 2. Détection maintenance prévue dans les N jours
            elseif ($infra->date_prochaine_maintenance && $infra->date_prochaine_maintenance->between($aujourdhui, $seuilFutur)) {
                $joursRestants = (int) $aujourdhui->diffInDays($infra->date_prochaine_maintenance->startOfDay());
                $type = 'maintenance_proche';
                $message = "Maintenance imminente dans {$joursRestants} jour(s) (prévue le {$infra->date_prochaine_maintenance->format('d/m/Y')}).";

                $cree = $this->enregistrerAlerteSiAbsente($infra->id, $type, $message);
                if ($cree) {
                    $alertesCreees++;
                }

                $tableRows[] = [
                    $infra->id,
                    $infra->nom,
                    $infra->zone?->nom ?? '—',
                    'Maintenance proche',
                    $message,
                ];
            }

            // 3. Détection durée de vie dépassée
            if ($infra->duree_vie_annees && $infra->date_installation) {
                $ageAnnees = (int) $infra->date_installation->diffInYears(now());
                if ($ageAnnees >= $infra->duree_vie_annees) {
                    $depassement = $ageAnnees - $infra->duree_vie_annees;
                    $type = 'duree_vie_depassee';
                    $message = "Durée de vie théorique dépassée de {$depassement} an(s) (installé en {$infra->date_installation->format('Y')}, durée : {$infra->duree_vie_annees} ans).";

                    $cree = $this->enregistrerAlerteSiAbsente($infra->id, $type, $message);
                    if ($cree) {
                        $alertesCreees++;
                    }

                    $tableRows[] = [
                        $infra->id,
                        $infra->nom,
                        $infra->zone?->nom ?? '—',
                        'Durée de vie dépassée',
                        $message,
                    ];
                }
            }
        }

        if (empty($tableRows)) {
            $this->info("Aucune alerte de maintenance détectée (anticipation à {$days} jours).");
        } else {
            $this->table(
                ['ID', 'Infrastructure', 'Zone', 'Type d’alerte', 'Description'],
                $tableRows
            );
            $this->info(count($tableRows) . " situation(s) signalée(s) dont {$alertesCreees} nouvelle(s) alerte(s) enregistrée(s).");
        }

        return Command::SUCCESS;
    }

    private function enregistrerAlerteSiAbsente(int $infrastructureId, string $type, string $message): bool
    {
        $existe = InfrastructureAlerte::where('infrastructure_id', $infrastructureId)
            ->where('type', $type)
            ->where('resolue', false)
            ->exists();

        if (! $existe) {
            InfrastructureAlerte::create([
                'infrastructure_id' => $infrastructureId,
                'type' => $type,
                'message' => $message,
                'resolue' => false,
            ]);

            return true;
        }

        return false;
    }
}
