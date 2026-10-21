<?php

namespace App\Observers;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\User;
use App\Notifications\InterventionAssignee;
use App\Notifications\InterventionModifiee;
use App\Notifications\InterventionTerminee;
use App\Services\FailureRiskService;
use App\Services\InterventionPlanningService;
use App\Services\PreventiveMaintenanceService;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class InterventionObserver
{
    public function updating(Intervention $intervention): void
    {
        if ($intervention->isDirty('infrastructure_id')) {
            $oldInfrastructureId = $intervention->getOriginal('infrastructure_id');
            $intervention->ancienneInfrastructureId = $oldInfrastructureId === null ? null : (int) $oldInfrastructureId;
        }
    }

    /**
     * Interventions dont l'enregistrement en cours est une création.
     * (wasRecentlyCreated reste vrai pour toute la vie de l'objet, il ne distingue pas une mise à jour qui suit)
     *
     * @var \WeakMap<Intervention, true>|null
     */
    private static ?\WeakMap $creations = null;

    public function created(Intervention $intervention): void
    {
        self::$creations ??= new \WeakMap();
        self::$creations[$intervention] = true;
    }

    /**
     * Dès qu'une intervention est enregistrée (création ou modification),
     * si elle est rattachée à une infrastructure et marquée comme 'Terminée',
     * on synchronise la date de dernière maintenance de l'équipement si celle-ci est plus récente.
     */
    public function saved(Intervention $intervention): void
    {
        if ($intervention->infrastructure_id && $intervention->statut === 'Terminée') {
            $infrastructure = Infrastructure::find($intervention->infrastructure_id);

            if ($infrastructure && $intervention->date) {
                if (! $infrastructure->date_derniere_maintenance || $intervention->date->gt($infrastructure->date_derniere_maintenance)) {
                    $infrastructure->date_derniere_maintenance = $intervention->date;

                    // Cycle préventif : la maintenance terminée fixe la prochaine échéance
                    if ($intervention->estPreventive()) {
                        $infrastructure->date_prochaine_maintenance = app(PreventiveMaintenanceService::class)
                            ->prochaineEcheance($infrastructure, $intervention->date);
                    }

                    $infrastructure->saveQuietly();
                }
            }
        }

        // Recalcul du risque de panne de l'équipement (module Infrastructures)
        $this->recomputeRisk(
            app(FailureRiskService::class),
            $intervention->infrastructure_id === null ? null : (int) $intervention->infrastructure_id,
            $intervention->ancienneInfrastructureId,
        );
        $intervention->ancienneInfrastructureId = null;

        // Après la mise à jour de l'infrastructure, pour que les notifications portent la nouvelle échéance
        $this->notifier($intervention);
    }

    public function deleted(Intervention $intervention): void
    {
        $this->recomputeRisk(
            app(FailureRiskService::class),
            $intervention->infrastructure_id === null ? null : (int) $intervention->infrastructure_id,
        );
    }

    private function recomputeRisk(FailureRiskService $riskService, ?int ...$infrastructureIds): void
    {
        foreach (array_unique(array_filter($infrastructureIds)) as $infrastructureId) {
            $infrastructure = Infrastructure::find($infrastructureId);
            if ($infrastructure) {
                $riskService->recompute($infrastructure);
            }
        }
    }

    /**
     * Notifie le technicien (affectation, replanification, annulation) et les gestionnaires (fin d'intervention).
     */
    private function notifier(Intervention $intervention): void
    {
        $actif = in_array($intervention->statut, InterventionPlanningService::STATUTS_ACTIFS, true);
        $creee = isset(self::$creations[$intervention]);
        if ($creee) {
            unset(self::$creations[$intervention]);
        }

        if (! $creee && ! $intervention->wasChanged(['technicien_id', 'statut', 'date'])) {
            return;
        }

        $intervention->load(['infrastructure', 'technicien.user']);
        $compte = $intervention->technicien?->user;

        if ($compte && $actif && ($creee || $intervention->wasChanged('technicien_id'))) {
            $this->envoyer($compte, new InterventionAssignee($intervention));
        } elseif ($compte && ! $creee && $intervention->wasChanged('statut') && $intervention->statut === 'Annulée') {
            $this->envoyer($compte, new InterventionModifiee($intervention));
        } elseif ($compte && ! $creee && $actif && $intervention->wasChanged('date')) {
            $this->envoyer($compte, new InterventionModifiee($intervention, Carbon::parse($intervention->getOriginal('date'))));
        }

        if (! $creee && $intervention->wasChanged('statut') && $intervention->statut === 'Terminée') {
            $gestionnaires = User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('slug', Role::MANAGER))
                ->get();

            $this->envoyer($gestionnaires, new InterventionTerminee($intervention));
        }
    }

    /**
     * Un échec d'envoi (SMTP mal configuré…) ne doit jamais empêcher l'enregistrement de l'intervention.
     */
    private function envoyer(mixed $destinataires, Notification $notification): void
    {
        rescue(fn () => NotificationFacade::send($destinataires, $notification));
    }
}
