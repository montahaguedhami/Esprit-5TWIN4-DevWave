<?php

namespace App\Services\Monitoring;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Services\InterventionPlanningService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Métriques de l'application au format d'exposition Prometheus (text/plain, version 0.0.4).
 *
 * Chaque famille est calculée séparément : une erreur (base indisponible…) n'empêche pas
 * l'exposition des autres, et se lit directement dans aquasecure_database_up.
 */
class MetricsCollector
{
    /** @var list<array{name: string, help: string, type: string, samples: list<array{0: array<string, string>, 1: float|int}>}> */
    private array $familles = [];

    public function render(): string
    {
        $this->familles = [];

        $this->ajouter('aquasecure_build_info', 'Informations sur la version déployée', 'gauge', [[[
            'app_env' => (string) app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ], 1]]);

        $baseOk = $this->base();
        if ($baseOk) {
            $this->metier();
            $this->fileAttente();
        }

        $this->ajouter('aquasecure_ollama_up', 'Serveur IA local Ollama joignable (1) ou non (0)', 'gauge',
            [[[], $this->ollamaJoignable() ? 1 : 0]]);
        $this->ajouter('aquasecure_gemini_configured', 'Clé Gemini présente pour les assistants IA des incidents', 'gauge',
            [[[], filled(config('services.gemini.api_key')) ? 1 : 0]]);

        return $this->formater();
    }

    private function base(): bool
    {
        $debut = microtime(true);
        $ok = rescue(fn () => DB::select('select 1') !== [], false, false);
        $this->ajouter('aquasecure_database_up', 'Base de données joignable (1) ou non (0)', 'gauge', [[[], $ok ? 1 : 0]]);
        $this->ajouter('aquasecure_database_latency_seconds', 'Durée d\'une requête simple à la base', 'gauge',
            [[[], round(microtime(true) - $debut, 6)]]);

        return $ok;
    }

    private function metier(): void
    {
        $this->ajouter('aquasecure_users_total', 'Comptes utilisateurs par rôle', 'gauge',
            Role::query()->withCount('users')->orderBy('slug')->get()
                ->map(fn (Role $r) => [['role' => $r->slug], $r->users_count])->all());

        $this->ajouter('aquasecure_interventions_total', 'Interventions par statut', 'gauge',
            $this->parValeur(Intervention::query(), 'statut'));

        $this->ajouter('aquasecure_interventions_overdue_total', 'Interventions actives dont la date est dépassée', 'gauge', [[[],
            Intervention::query()->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
                ->whereDate('date', '<', today()->toDateString())->count(),
        ]]);

        $this->ajouter('aquasecure_preventive_maintenance_due_total', 'Infrastructures dont la maintenance préventive est due sous 7 jours', 'gauge', [[[],
            Infrastructure::query()->whereNotNull('date_prochaine_maintenance')
                ->whereDate('date_prochaine_maintenance', '<=', today()->addDays(7)->toDateString())->count(),
        ]]);

        $this->ajouter('aquasecure_incidents_total', 'Incidents signalés par statut', 'gauge',
            $this->parValeur(Incident::query(), 'statut'));
    }

    private function fileAttente(): void
    {
        $this->ajouter('aquasecure_queue_jobs_pending', 'Tâches en attente dans la file (notifications…)', 'gauge',
            [[[], rescue(fn () => DB::table('jobs')->count(), 0, false)]]);
        $this->ajouter('aquasecure_queue_jobs_failed_total', 'Tâches de la file en échec', 'gauge',
            [[[], rescue(fn () => DB::table('failed_jobs')->count(), 0, false)]]);
    }

    /**
     * Ollama est interrogé au plus une fois par minute : la collecte reste rapide même s'il est arrêté.
     */
    private function ollamaJoignable(): bool
    {
        if (! config('ollama.enabled', true)) {
            return false;
        }

        return Cache::remember('monitoring:ollama_up', 60, fn () => rescue(
            fn () => Http::timeout(2)->get(rtrim((string) config('ollama.url'), '/').'/api/tags')->successful(),
            false,
            false
        ));
    }

    /**
     * @return list<array{0: array<string, string>, 1: int}>
     */
    private function parValeur($requete, string $colonne): array
    {
        return $requete->toBase()->select($colonne, DB::raw('count(*) as total'))->groupBy($colonne)->orderBy($colonne)->get()
            ->map(fn ($ligne) => [[$colonne => (string) $ligne->{$colonne}], (int) $ligne->total])->all();
    }

    private function ajouter(string $nom, string $aide, string $type, array $echantillons): void
    {
        $this->familles[] = ['name' => $nom, 'help' => $aide, 'type' => $type, 'samples' => $echantillons];
    }

    private function formater(): string
    {
        $lignes = [];
        foreach ($this->familles as $famille) {
            $lignes[] = "# HELP {$famille['name']} ".str_replace(["\\", "\n"], ['\\\\', '\\n'], $famille['help']);
            $lignes[] = "# TYPE {$famille['name']} {$famille['type']}";
            foreach ($famille['samples'] as [$labels, $valeur]) {
                $etiquettes = collect($labels)
                    ->map(fn ($v, $k) => $k.'="'.str_replace(["\\", '"', "\n"], ['\\\\', '\\"', '\\n'], $v).'"')
                    ->implode(',');
                $lignes[] = $famille['name'].($etiquettes !== '' ? '{'.$etiquettes.'}' : '').' '.$valeur;
            }
        }

        return implode("\n", $lignes)."\n";
    }
}
