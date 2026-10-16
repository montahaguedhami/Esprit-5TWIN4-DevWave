@extends('layouts.admin')

@section('admin-content')
<div class="aqua-admin-page min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/40 text-red-200">
            <div class="flex items-center gap-3 mb-1">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-red-400"></i>
                <span class="font-semibold">{{ session('error') ?? 'Une erreur est survenue :' }}</span>
            </div>
            @if($errors->any())
            <ul class="list-disc list-inside text-sm text-red-300 ml-8">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="shield" class="w-8 h-8 text-purple-400"></i>
                    Rôles & Permissions AquaSecure
                </h1>
                <p class="text-slate-400">Les 4 rôles officiels de référence avec contrôle d'accès strict par moindre privilège</p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 text-xs font-semibold">
                <i data-lucide="lock" class="w-4 h-4"></i>
                4 Rôles Officiels Définis
            </div>
        </div>

        @php
            // Load real DB roles if not already injected
            $roles = $roles ?? \App\Models\Role::whereIn('slug', \App\Models\Role::OFFICIAL_ROLES)->with(['permissions', 'users'])->get();
            $allPermissions = $allPermissions ?? \App\Models\Permission::all()->groupBy('module');
        @endphp

        <!-- Roles Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            @foreach($roles as $role)
            @php
                $color = match($role->slug) {
                    'administrateur' => 'purple',
                    'manager' => 'blue',
                    'technicien' => 'cyan',
                    'citoyen' => 'emerald',
                    default => 'cyan',
                };
                $permsByModule = $role->permissions->groupBy('module');
                $rolePermsArray = $role->permissions->pluck('name')->toArray();
            @endphp
            <x-ui.card>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-{{ $color }}-500/20 flex items-center justify-center">
                            <i data-lucide="shield" class="w-6 h-6 text-{{ $color }}-400"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-white">{{ $role->name }}</h3>
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-mono">
                                    {{ $role->slug }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-400">{{ $role->description }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-6 mb-4 pb-4 border-b border-slate-700">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ $role->users->count() }}</div>
                        <div class="text-xs text-slate-400">Utilisateurs assignés</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ $role->permissions->count() }}</div>
                        <div class="text-xs text-slate-400">Permissions actives</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ $permsByModule->count() }}</div>
                        <div class="text-xs text-slate-400">Modules couverts</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400 font-medium">Permissions par module</span>
                        <span class="text-xs text-cyan-400 font-mono">{{ $role->permissions->count() }} total</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($permsByModule as $module => $perms)
                        <div class="p-2 rounded-lg bg-slate-800/60 border border-slate-700/50">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold text-white capitalize">{{ $module }}</span>
                                <span class="text-[10px] text-cyan-400 font-medium">{{ count($perms) }}</span>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($perms as $perm)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700/80 text-slate-300" title="{{ $perm->label }}">
                                    {{ explode('.', $perm->name)[1] ?? $perm->name }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700">
                    <button onclick="openConfigureModal({{ $role->id }}, '{{ $role->name }}', '{{ $role->slug }}', {{ json_encode($rolePermsArray) }})" class="w-full btn btn-secondary text-sm flex items-center justify-center gap-2">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Configurer les permissions de ce rôle
                    </button>
                </div>
            </x-ui.card>
            @endforeach
        </div>

        <!-- Permissions Matrix -->
        <x-ui.card>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="grid" class="w-6 h-6 text-cyan-400"></i>
                        Matrice Officielle des Permissions RBAC
                    </h2>
                    <p class="text-xs text-slate-400">Comparaison détaillée des autorisations accordées par rôle</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1 text-emerald-400"><i data-lucide="check" class="w-3.5 h-3.5"></i> Autorisé</span>
                    <span class="flex items-center gap-1 text-slate-600"><i data-lucide="x" class="w-3.5 h-3.5"></i> Refusé par défaut</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Module & Action</th>
                            @foreach($roles as $role)
                            @php
                                $col = match($role->slug) {
                                    'administrateur' => 'purple',
                                    'manager' => 'blue',
                                    'technicien' => 'cyan',
                                    'citoyen' => 'emerald',
                                    default => 'cyan',
                                };
                            @endphp
                            <th class="text-center py-3 px-4 text-sm font-semibold text-{{ $col }}-400">
                                {{ $role->name }}
                                <div class="text-[10px] text-slate-400 font-mono">{{ $role->slug }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allPermissions as $module => $modulePerms)
                        <tr class="bg-slate-800/40 border-b border-slate-700/60">
                            <td colspan="{{ 1 + count($roles) }}" class="py-2 px-4 text-xs font-bold text-cyan-300 uppercase tracking-wider">
                                Module : {{ $module }}
                            </td>
                        </tr>
                        @foreach($modulePerms as $perm)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="py-2.5 px-4">
                                <div class="font-medium text-white text-sm">{{ $perm->label }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $perm->name }}</div>
                            </td>
                            @foreach($roles as $role)
                            @php
                                $has = $role->permissions->contains('name', $perm->name);
                                $col = match($role->slug) {
                                    'administrateur' => 'purple',
                                    'manager' => 'blue',
                                    'technicien' => 'cyan',
                                    'citoyen' => 'emerald',
                                    default => 'cyan',
                                };
                            @endphp
                            <td class="py-2.5 px-4 text-center">
                                @if($has)
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-{{ $col }}-500/20 text-{{ $col }}-400" title="{{ $role->name }} autorise {{ $perm->name }}">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </span>
                                @else
                                <span class="inline-flex items-center justify-center w-6 h-6 text-slate-600">
                                    —
                                </span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</div>

<!-- Modal Configuration Permissions Rôle -->
<div id="configureRoleModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800 shrink-0">
            <div>
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="shield" class="w-5 h-5 text-cyan-400"></i>
                    Modifier les permissions : <span id="modal_role_name" class="text-cyan-400"></span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Identifiant technique : <span id="modal_role_slug" class="font-mono"></span></p>
            </div>
            <button onclick="closeConfigureModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form id="configureRoleForm" method="POST" action="" class="flex-1 flex flex-col min-h-0">
            @csrf
            @method('PUT')

            <div class="flex-1 overflow-y-auto space-y-4 pr-2">
                @foreach($allPermissions as $module => $modulePerms)
                <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700">
                    <h4 class="text-sm font-bold text-white capitalize mb-3 flex items-center gap-2">
                        <i data-lucide="box" class="w-4 h-4 text-cyan-400"></i>
                        Module {{ $module }}
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($modulePerms as $p)
                        <label class="flex items-start gap-2 p-2 rounded-lg hover:bg-slate-700/50 cursor-pointer transition-colors text-xs text-slate-300">
                            <input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="role-perm-checkbox mt-0.5 rounded bg-slate-900 border-slate-600 text-cyan-500 focus:ring-0" data-perm="{{ $p->name }}">
                            <div>
                                <div class="font-medium text-white">{{ $p->label }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $p->name }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-800 mt-4 shrink-0">
                <span class="text-xs text-slate-400">Les modifications prennent effet immédiatement.</span>
                <div class="flex gap-3">
                    <button type="button" onclick="closeConfigureModal()" class="btn btn-secondary text-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary text-sm">Sauvegarder les permissions</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openConfigureModal(roleId, roleName, roleSlug, currentPerms) {
        document.getElementById('modal_role_name').textContent = roleName;
        document.getElementById('modal_role_slug').textContent = roleSlug;
        document.getElementById('configureRoleForm').action = `/admin/roles/${roleId}/permissions`;

        // Check the boxes for current permissions
        const checkboxes = document.querySelectorAll('.role-perm-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = currentPerms.includes(cb.getAttribute('data-perm'));
        });

        document.getElementById('configureRoleModal').classList.remove('hidden');
    }

    function closeConfigureModal() {
        document.getElementById('configureRoleModal').classList.add('hidden');
    }
</script>
@endsection
