<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

        $userId = isset($user['id']) ? (int) $user['id'] : null;

        if ($userId) {
            $linkedTechnician = Technicien::where('user_id', $userId)->first();
            if ($linkedTechnician) {
                return $linkedTechnician;
            }
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
            $tech = Technicien::where(function (Builder $query) use ($cleanName): void {
                $query->where('nom', $cleanName)
                    ->orWhere('nom', 'like', "%{$cleanName}%");
            })
                ->when($userId, fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('user_id')->orWhere('user_id', $userId)))
                ->first();
            if ($tech) {
                if ($userId && ! $tech->user_id) {
                    $tech->forceFill(['user_id' => $userId])->save();
                }

                return $tech;
            }
        }

        // 3. Fallback automatique pour environnement démo ou tests
        $identity = $userId ? ['user_id' => $userId] : ['nom' => $cleanName ?? 'Amira Ben Ali'];

        return Technicien::firstOrCreate(
            $identity,
            [
                'nom' => $cleanName ?? 'Amira Ben Ali',
                'specialite' => 'Canalisations',
                'telephone' => '+216 22 123 456',
                'disponibilite' => 'Disponible',
            ]
        );
    }

    /**
     * Retourne uniquement les fiches disponibles rattachées à un compte technicien actif.
     */
    public static function availableTechniciansForAssignment(): Collection
    {
        $technicianUsers = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->where('slug', Role::TECHNICIEN))
            ->orderBy('name')
            ->get();

        foreach ($technicianUsers as $user) {
            $technician = Technicien::where('user_id', $user->id)->first();
            if ($technician) {
                continue;
            }

            $technician = Technicien::where('nom', $user->name)->whereNull('user_id')->first();
            if ($technician) {
                $technician->user()->associate($user);
                $technician->save();
                continue;
            }

            Technicien::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nom' => $user->name,
                    'specialite' => 'Canalisations',
                    'telephone' => 'Non renseigné',
                    'disponibilite' => 'Disponible',
                ]
            );
        }

        return Technicien::query()
            ->with('user')
            ->where('disponibilite', 'Disponible')
            ->whereHas('user', fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('roles', fn (Builder $roles) => $roles->where('slug', Role::TECHNICIEN)))
            ->orderBy('nom')
            ->get();
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
