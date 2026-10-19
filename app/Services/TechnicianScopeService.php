<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Database\Eloquent\Builder;

class TechnicianScopeService
{
    /**
     * Résout le modèle Technicien correspondant à la session active.
     */
    public static function currentTechnicien(): ?Technicien
    {
        $user = session('user');
        if (! $user) {
            return null;
        }

        // 1. Si l'ID du technicien est explicitement en session
        if (! empty($user['technicien_id'])) {
            $tech = Technicien::find($user['technicien_id']);
            if ($tech) {
                return $tech;
            }
        }

        // 2. Recherche par nom (nettoyé de libellés additionnels comme "(Field Technician)")
        $name = $user['name'] ?? null;
        if ($name) {
            $cleanName = trim(explode('(', $name)[0]);
            $tech = Technicien::where('nom', $cleanName)
                ->orWhere('nom', 'like', "%{$cleanName}%")
                ->first();
            if ($tech) {
                return $tech;
            }
        }

        // 3. Fallback automatique pour environnement démo ou tests
        return Technicien::firstOrCreate(
            ['nom' => $cleanName ?? 'Amira Ben Ali'],
            [
                'specialite' => 'Canalisations',
                'telephone' => '+216 22 123 456',
                'disponibilite' => 'Disponible',
            ]
        );
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
        return \App\Models\Zone::query()
            ->whereHas('infrastructures.interventions', function (Builder $query) use ($technicien): void {
                $query->where('technicien_id', $technicien->id);
            })
            ->withCount(['infrastructures' => function (Builder $query) use ($technicien): void {
                $query->whereHas('interventions', function (Builder $interventions) use ($technicien): void {
                    $interventions->where('technicien_id', $technicien->id);
                });
            }]);
    }

    /**
     * Vérifie si le technicien peut consulter cette zone.
     */
    public static function canAccessZone(Technicien $technicien, \App\Models\Zone $zone): bool
    {
        return self::zonesQuery($technicien)->whereKey($zone->getKey())->exists();
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
