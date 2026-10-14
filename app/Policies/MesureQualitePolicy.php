<?php

namespace App\Policies;

use App\Models\MesureQualite;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MesureQualitePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermission('measurements.view')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de consulter les mesures de qualité.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MesureQualite $mesure): Response
    {
        return $user->hasPermission('measurements.view')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de consulter cette mesure.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermission('measurements.create')
            ? Response::allow()
            : Response::deny('Seuls les techniciens et administrateurs autorisés peuvent enregistrer de nouvelles mesures.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MesureQualite $mesure): Response
    {
        if (! $user->hasPermission('measurements.update')) {
            return Response::deny('Vous n\'avez pas la permission de modifier cette mesure.');
        }

        // Si technicien, ne peut modifier qu'une mesure non encore vérifiée
        if ($user->hasRole(Role::TECHNICIEN) && ! $user->hasRole([Role::MANAGER, Role::ADMINISTRATEUR])) {
            if ($mesure->is_verified) {
                return Response::deny('Cette mesure a déjà été validée et ne peut plus être modifiée par un technicien.');
            }
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can validate measurements.
     */
    public function validate(User $user, MesureQualite $mesure): Response
    {
        // Strict business rule: Technicians and Citizens CANNOT validate measurements.
        // Only Manager and Administrateur can validate!
        if (! $user->hasPermission('measurements.validate')) {
            return Response::deny('Validation non autorisée : seuls les gestionnaires ou administrateurs peuvent valider les mesures.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MesureQualite $mesure): Response
    {
        return $user->hasPermission('measurements.delete')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de supprimer des mesures de qualité.');
    }
}
