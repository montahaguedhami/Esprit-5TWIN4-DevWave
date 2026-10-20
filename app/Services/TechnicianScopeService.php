<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TechnicianScopeService
{
    /**
     * Résout le modèle Technicien correspondant à la session active.
     */
    public static function currentTechnicien(): ?Technicien
    {
        // 1. Compte connecté : la fiche est retrouvée par sa clé étrangère user_id
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
            $tech = Technicien::find($technicienId);
            if ($tech) {
                return $tech;
            }
        }

        // 3. Compte réel sans fiche liée : pas de création ni de rattachement par nom (403)
        if ($userId) {
            return null;
        }

        // 4. Session de démonstration sans compte (/switch-role), jamais en production
        if (app()->environment(['local', 'testing'])) {
            return self::demoTechnicienFromSessionName(session('user.name'));
        }

        // Aucun rattachement : les contrôleurs répondent 403
        return null;
    }

    private static function demoTechnicienFromSessionName(?string $name): ?Technicien
    {
        if (! $name) {
            return null;
        }

        // Nettoie les libellés additionnels comme "(Field Technician)"
        $cleanName = trim(explode('(', $name)[0]);
        if ($cleanName === '') {
            return null;
        }

        $tech = Technicien::where(function (Builder $query) use ($cleanName): void {
            $query->where('nom', $cleanName)
                ->orWhere('nom', 'like', "%{$cleanName}%");
        })->first();

        return $tech ?? Technicien::firstOrCreate(
            ['nom' => $cleanName],
            [
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
