<?php

namespace App\Console\Commands;

use App\Services\PreventiveMaintenanceService;
use Illuminate\Console\Command;

class PlanifierMaintenancePreventive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:planifier-preventive
        {--jours= : Horizon en jours (défaut : config maintenance.horizon_jours)}
        {--simulation : Affiche ce qui serait planifié sans rien créer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée les interventions préventives des infrastructures arrivant à échéance et les affecte au technicien le plus adapté.';

    public function handle(PreventiveMaintenanceService $service): int
    {
        $horizon = $this->option('jours') !== null ? max(0, (int) $this->option('jours')) : null;
        $simulation = (bool) $this->option('simulation');

        $lignes = $service->planifier($horizon, $simulation);

        if ($lignes->isEmpty()) {
            $this->info('Aucune maintenance préventive à planifier.');

            return self::SUCCESS;
        }

        $this->table(
            ['Infrastructure', 'Échéance', 'Résultat', 'Date', 'Technicien'],
            $lignes->map(fn (array $ligne) => [
                $ligne['infrastructure']->nom,
                $ligne['echeance']->format('d/m/Y'),
                match ($ligne['resultat']) {
                    PreventiveMaintenanceService::PLANIFIEE => $simulation ? 'À planifier' : 'Planifiée',
                    PreventiveMaintenanceService::DEJA_PLANIFIEE => 'Déjà planifiée',
                    default => 'Aucun technicien disponible',
                },
                $ligne['date']?->format('d/m/Y') ?? '—',
                $ligne['technicien']
                    ? $ligne['technicien']->nom.($ligne['hors_specialite'] ? ' (hors spécialité)' : '')
                    : '—',
            ])->all()
        );

        $planifiees = $lignes->where('resultat', PreventiveMaintenanceService::PLANIFIEE)->count();
        $this->info($simulation
            ? "Simulation : {$planifiees} intervention(s) seraient créées."
            : "{$planifiees} intervention(s) préventive(s) créée(s).");

        return self::SUCCESS;
    }
}
