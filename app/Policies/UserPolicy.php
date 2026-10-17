<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermission('users.view')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de consulter les utilisateurs.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        if ($user->id === $model->id) {
            return Response::allow();
        }

        return $user->hasPermission('users.view')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de consulter cet utilisateur.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasPermission('users.create')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de créer des utilisateurs.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        if ($user->id === $model->id) {
            return Response::allow();
        }

        return $user->hasPermission('users.update')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas la permission de modifier cet utilisateur.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        if (! $user->hasPermission('users.delete')) {
            return Response::deny('Vous n\'avez pas la permission de supprimer des utilisateurs.');
        }

        if ($user->id === $model->id) {
            return Response::deny('Protection de sécurité : vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($model->isLastAdministrator()) {
            return Response::deny('Protection administrateur : impossible de supprimer le dernier administrateur actif du système.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRoles(User $user, User $model, array $targetRoles = []): Response
    {
        if (! $user->hasPermission('users.assign_roles')) {
            return Response::deny('Vous n\'avez pas la permission d\'attribuer des rôles.');
        }

        // Interdire l'auto-attribution de rôles si non administrateur
        if ($user->id === $model->id && ! $user->isAdministrator()) {
            return Response::deny('Interdiction d\'élévation de privilèges : vous ne pouvez pas modifier vos propres rôles.');
        }

        // Seul un administrateur peut accorder le rôle administrateur
        $normalizedTargets = array_map(fn ($r) => Role::normalizeSlug($r), $targetRoles);
        if (in_array(Role::ADMINISTRATEUR, $normalizedTargets, true) && ! $user->isAdministrator()) {
            return Response::deny('Seul un administrateur peut attribuer le rôle administrateur.');
        }

        // Protection du dernier administrateur contre la rétrogradation
        if ($model->isLastAdministrator() && ! in_array(Role::ADMINISTRATEUR, $normalizedTargets, true)) {
            return Response::deny('Protection administrateur : impossible de retirer le rôle administrateur au dernier administrateur actif.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can toggle status (active/inactive).
     */
    public function toggleStatus(User $user, User $model): Response
    {
        if (! $user->hasPermission('users.toggle_status')) {
            return Response::deny('Vous n\'avez pas la permission de modifier le statut des utilisateurs.');
        }

        if ($user->id === $model->id) {
            return Response::deny('Vous ne pouvez pas désactiver votre propre compte.');
        }

        if ($model->isLastAdministrator() && $model->is_active) {
            return Response::deny('Protection administrateur : impossible de désactiver le dernier administrateur actif.');
        }

        return Response::allow();
    }
}
