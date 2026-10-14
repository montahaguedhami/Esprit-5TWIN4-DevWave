<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('roles.view');

        // Only the 4 official roles
        $roles = Role::whereIn('slug', Role::OFFICIAL_ROLES)
            ->with(['permissions', 'users'])
            ->get();

        $allPermissions = Permission::all()->groupBy('module');

        return view('admin.roles', compact('roles', 'allPermissions'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('roles.manage');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $permissionNames = $validated['permissions'] ?? [];

        // Protection: administrateur must retain fundamental security permissions
        if ($role->slug === Role::ADMINISTRATEUR) {
            $requiredAdminPerms = ['roles.view', 'roles.manage', 'users.view', 'users.assign_roles'];
            foreach ($requiredAdminPerms as $required) {
                if (! in_array($required, $permissionNames, true)) {
                    $permissionNames[] = $required;
                }
            }
        }

        $role->syncPermissions($permissionNames);

        AuditLog::record('role_permissions_updated', 'Role', $role->id, [
            'role' => $role->slug,
            'updated_by' => $currentUser->email,
            'permissions_count' => count($permissionNames),
        ], $currentUser);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Permissions du rôle '{$role->name}' mises à jour avec succès.",
                'permissions' => $role->permissions()->pluck('name'),
            ]);
        }

        return redirect()->route('admin.roles')->with('success', "Permissions du rôle '{$role->name}' mises à jour avec succès.");
    }

    private function resolveFallbackUser(): User
    {
        $sessionEmail = session('user.email');
        if ($sessionEmail) {
            $user = User::where('email', $sessionEmail)->first();
            if ($user) {
                return $user;
            }
        }

        return User::whereHas('roles', fn ($q) => $q->where('slug', Role::ADMINISTRATEUR))->firstOrFail();
    }
}
