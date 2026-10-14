<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('viewAny', User::class);

        $query = User::with('roles')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $roleSlug = Role::normalizeSlug($request->input('role'));
            $query->whereHas('roles', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            });
        }

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->paginate(15)->withQueryString();

        $allUsers = User::with('roles')->get();
        $userStats = [
            'total' => $allUsers->count(),
            'active' => $allUsers->where('is_active', true)->count(),
            'admins' => $allUsers->filter(fn ($u) => $u->hasRole(Role::ADMINISTRATEUR))->count(),
            'managers' => $allUsers->filter(fn ($u) => $u->hasRole(Role::MANAGER))->count(),
            'technicians' => $allUsers->filter(fn ($u) => $u->hasRole(Role::TECHNICIEN))->count(),
            'citizens' => $allUsers->filter(fn ($u) => $u->hasRole(Role::CITOYEN))->count(),
        ];

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'userStats', 'roles'));
    }

    public function store(Request $request)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(array_merge(Role::OFFICIAL_ROLES, ['admin', 'technician', 'citizen']))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $normalizedRole = Role::normalizeSlug($validated['role']);

        // Check privilege escalation: only an administrator can create another administrator
        if ($normalizedRole === Role::ADMINISTRATEUR && ! $currentUser->isAdministrator()) {
            throw ValidationException::withMessages([
                'role' => ['Interdiction : seul un administrateur peut créer un compte avec le rôle administrateur.'],
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($normalizedRole);

        AuditLog::record('user_created', 'User', $user->id, [
            'created_by' => $currentUser->email,
            'assigned_role' => $normalizedRole,
            'user_email' => $user->email,
        ], $currentUser);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Utilisateur créé avec succès.',
                'user' => $user->load('roles'),
            ], 201);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string', Rule::in(array_merge(Role::OFFICIAL_ROLES, ['admin', 'technician', 'citizen']))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($request->has('is_active')) {
            $newStatus = $request->boolean('is_active');
            if (! $newStatus && $user->isLastAdministrator()) {
                throw ValidationException::withMessages([
                    'is_active' => ['Protection administrateur : impossible de désactiver le dernier administrateur actif.'],
                ]);
            }
            $updateData['is_active'] = $newStatus;
        }

        $user->update($updateData);

        if (! empty($validated['role'])) {
            $targetRole = Role::normalizeSlug($validated['role']);
            Gate::forUser($currentUser)->authorize('assignRoles', [$user, [$targetRole]]);
            $user->syncRoles([$targetRole]);
        }

        AuditLog::record('user_updated', 'User', $user->id, [
            'updated_by' => $currentUser->email,
            'user_email' => $user->email,
        ], $currentUser);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès.',
                'user' => $user->load('roles'),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('toggleStatus', $user);

        $newStatus = ! $user->is_active;

        if (! $newStatus && $user->isLastAdministrator()) {
            throw ValidationException::withMessages([
                'status' => ['Protection administrateur : impossible de désactiver le dernier administrateur actif.'],
            ]);
        }

        $user->is_active = $newStatus;
        $user->save();

        AuditLog::record('user_status_toggled', 'User', $user->id, [
            'new_status' => $newStatus ? 'active' : 'inactive',
            'toggled_by' => $currentUser->email,
            'user_email' => $user->email,
        ], $currentUser);

        $statusText = $newStatus ? 'activé' : 'désactivé';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Le compte de {$user->name} a été {$statusText}.",
                'is_active' => $user->is_active,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', "Le compte de {$user->name} a été {$statusText}.");
    }

    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user() ?? $this->resolveFallbackUser();
        Gate::forUser($currentUser)->authorize('delete', $user);

        $deletedEmail = $user->email;
        $user->delete();

        AuditLog::record('user_deleted', 'User', null, [
            'deleted_user_email' => $deletedEmail,
            'deleted_by' => $currentUser->email,
        ], $currentUser);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé avec succès.');
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
