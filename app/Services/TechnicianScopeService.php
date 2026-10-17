<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TechnicianScopeService
{
    /**
     * Résout le modèle Technicien correspondant à la session active.
     */
    public static function currentTechnicien(): ?Technicien
    {
        // 1. Compte connecté : la fiche est retrouvée par sa clé étrangère user_id (jamais par le nom)
        $userId = Auth::id() ?? session('user.id');
        if ($userId) {
            $tech = Technicien::where('user_id', $userId)->first();
            if ($tech) {
                return $tech;
            }
        }

        // 2. Identifiant de fiche placé en session côté serveur (tests de fonctionnalités)
        $technicienId = session('user.technicien_id');
        if ($technicienId) {
            return Technicien::find($technicienId);
        }

        // Aucun rattachement : les contrôleurs répondent 403
        return null;
    }

    /**
     * Requête des infrastructures associées aux interventions du technicien.
     */
    public static function infrastructuresQuery(Technicien $technicien): Builder
    {
        return Infrastructure::query()
            ->whereHas('interventions', function (Builder $query) use ($technicien): void {
                $query->where('technicien_id', $technicien->id);
            })
            ->with(['zone']);
    }

    /**
     * Requête des zones d'intervention du technicien.
     */
    public static function zonesQuery(Technicien $technicien): Builder
    {
        $hasScopedZones = \App\Models\Zone::whereHas('infrastructures.interventions', function (Builder $query) use ($technicien): void {
            $query->where('technicien_id', $technicien->id);
        })->exists();

        // Si le technicien a des infrastructures assignées, limiter à ses zones, sinon afficher les zones actives
        $builder = \App\Models\Zone::query();
        if ($hasScopedZones) {
            $builder->whereHas('infrastructures.interventions', function (Builder $query) use ($technicien): void {
                $query->where('technicien_id', $technicien->id);
            });
        }

        return $builder->withCount(['infrastructures' => function (Builder $query) use ($technicien, $hasScopedZones): void {
            if ($hasScopedZones) {
                $query->whereHas('interventions', function (Builder $q) use ($technicien): void {
                    $q->where('technicien_id', $technicien->id);
                });
            }
        }]);
    }

    /**
     * Vérifie si le technicien peut consulter cette zone.
     */
    public static function canAccessZone(Technicien $technicien, \App\Models\Zone $zone): bool
    {
        return true; // En consultation terrain, toutes les zones sectorielles sont lisibles
    }

    /**
     * Vérifie si le technicien est autorisé à consulter cette infrastructure.
     */
    public static function canAccessInfrastructure(Technicien $technicien, Infrastructure $infrastructure): bool
    {
        return $infrastructure->interventions()
            ->where('technicien_id', $technicien->id)
            ->exists();
    }

    /**
     * Vérifie si le technicien dispose d'une intervention OUVERTE (Planifiée ou En cours) sur l'infrastructure.
     */
    public static function hasOpenIntervention(Technicien $technicien, Infrastructure $infrastructure): bool
    {
        return $infrastructure->interventions()
            ->where('technicien_id', $technicien->id)
            ->whereIn('statut', ['Planifiée', 'En cours'])
            ->exists();
    }

    /**
     * Récupère la première intervention ouverte sur l'infrastructure pour ce technicien.
     */
    public static function getOpenIntervention(Technicien $technicien, Infrastructure $infrastructure): ?Intervention
    {
        return $infrastructure->interventions()
            ->where('technicien_id', $technicien->id)
            ->whereIn('statut', ['Planifiée', 'En cours'])
            ->first();
    }
}
