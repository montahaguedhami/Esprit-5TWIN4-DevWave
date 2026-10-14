@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
<<<<<<< HEAD
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
=======
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
>>>>>>> user
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="users" class="w-8 h-8 text-cyan-400"></i>
                    Gestion des Utilisateurs
                </h1>
<<<<<<< HEAD
                <p class="text-slate-400">Gérer les comptes et permissions du système</p>
            </div>
            <button class="btn btn-primary">
=======
                <p class="text-slate-400">Contrôle d'accès RBAC et gestion des 4 rôles officiels AquaSecure</p>
            </div>
            <button onclick="openCreateUserModal()" class="btn btn-primary inline-flex items-center gap-2">
>>>>>>> user
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Nouvel Utilisateur
            </button>
        </div>

        <!-- Stats Cards -->
<<<<<<< HEAD
        @php
            $userStats = \App\Data\PlaceholderData::adminUserStats();
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <x-dashboard.kpi-card 
                icon="users" 
                label="Total Utilisateurs" 
                :value="$userStats['total']"
=======
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
            <x-dashboard.kpi-card 
                icon="users" 
                label="Total Utilisateurs" 
                :value="$userStats['total'] ?? 0"
>>>>>>> user
                iconColor="text-cyan-400"
            />
            <x-dashboard.kpi-card 
                icon="user-check" 
                label="Actifs" 
<<<<<<< HEAD
                :value="$userStats['active']"
=======
                :value="$userStats['active'] ?? 0"
>>>>>>> user
                iconColor="text-emerald-400"
            />
            <x-dashboard.kpi-card 
                icon="shield" 
                label="Administrateurs" 
<<<<<<< HEAD
                :value="$userStats['admins']"
=======
                :value="$userStats['admins'] ?? 0"
>>>>>>> user
                iconColor="text-purple-400"
            />
            <x-dashboard.kpi-card 
                icon="briefcase" 
                label="Gestionnaires" 
<<<<<<< HEAD
                :value="$userStats['managers']"
=======
                :value="$userStats['managers'] ?? 0"
>>>>>>> user
                iconColor="text-blue-400"
            />
            <x-dashboard.kpi-card 
                icon="wrench" 
                label="Techniciens" 
<<<<<<< HEAD
                :value="$userStats['technicians']"
                iconColor="text-cyan-400"
            />
=======
                :value="$userStats['technicians'] ?? 0"
                iconColor="text-cyan-400"
            />
            <x-dashboard.kpi-card 
                icon="user" 
                label="Citoyens" 
                :value="$userStats['citizens'] ?? 0"
                iconColor="text-emerald-400"
            />
>>>>>>> user
        </div>

        <!-- Filters -->
        <x-ui.card class="mb-6">
<<<<<<< HEAD
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <x-ui.search-input placeholder="Rechercher un utilisateur..." />
                </div>
                
                <x-forms.select name="role" class="w-48">
                    <option value="">Tous les rôles</option>
                    <option value="admin">Administrateur</option>
                    <option value="manager">Gestionnaire</option>
                    <option value="technician">Technicien</option>
                    <option value="citizen">Citoyen</option>
                </x-forms.select>

                <x-forms.select name="status" class="w-40">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </x-forms.select>

                <button class="btn btn-secondary">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exporter
                </button>
            </div>
        </x-ui.card>

        <!-- Users Table -->
        @php
            $users = \App\Data\PlaceholderData::adminUsers();
        @endphp

=======
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou email..." class="w-full pl-9 pr-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    </div>
                </div>
                
                <select name="role" class="w-48 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    <option value="">Tous les rôles</option>
                    <option value="administrateur" {{ request('role') === 'administrateur' ? 'selected' : '' }}>Administrateur</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Gestionnaire</option>
                    <option value="technicien" {{ request('role') === 'technicien' ? 'selected' : '' }}>Technicien</option>
                    <option value="citoyen" {{ request('role') === 'citoyen' ? 'selected' : '' }}>Citoyen</option>
                </select>

                <select name="status" class="w-40 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>

                <button type="submit" class="btn btn-secondary text-sm">
                    <i data-lucide="filter" class="w-4 h-4"></i> Filtrer
                </button>
                @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-400 hover:text-white">Réinitialiser</a>
                @endif
            </form>
        </x-ui.card>

        <!-- Users Table -->
>>>>>>> user
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Utilisateur</th>
<<<<<<< HEAD
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Rôle</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Statut</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Dernière connexion</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Contact</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-slate-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$user['name']" size="md" />
                                    <div>
                                        <div class="font-medium text-white">{{ $user['name'] }}</div>
                                        <div class="text-sm text-slate-400">{{ $user['email'] }}</div>
=======
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Rôle RBAC</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Statut</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Date création</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-slate-300">Actions de sécurité</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        @php
                            $primaryRole = $user->primary_role ?? 'citoyen';
                            $roleColors = [
                                'administrateur' => 'purple',
                                'manager' => 'blue',
                                'technicien' => 'cyan',
                                'citoyen' => 'emerald',
                            ];
                            $roleLabels = [
                                'administrateur' => 'Administrateur',
                                'manager' => 'Gestionnaire',
                                'technicien' => 'Technicien',
                                'citoyen' => 'Citoyen',
                            ];
                            $isLastAdmin = $user->isLastAdministrator();
                        @endphp
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center font-bold text-cyan-300 text-sm">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-white flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($isLastAdmin)
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30" title="Dernier administrateur actif protégé contre suppression et rétrogradation">
                                                Dernier Admin (Protégé)
                                            </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-slate-400">{{ $user->email }}</div>
>>>>>>> user
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
<<<<<<< HEAD
                                @php
                                    $roleColors = [
                                        'admin' => 'purple',
                                        'manager' => 'blue',
                                        'technician' => 'cyan',
                                        'citizen' => 'emerald',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Administrateur',
                                        'manager' => 'Gestionnaire',
                                        'technician' => 'Technicien',
                                        'citizen' => 'Citoyen',
                                    ];
                                @endphp
                                <x-ui.status-badge 
                                    :status="$roleColors[$user['role']]" 
                                    :label="$roleLabels[$user['role']]" 
                                />
                            </td>
                            <td class="py-4 px-4">
                                <x-ui.status-badge 
                                    :status="$user['status'] === 'active' ? 'success' : 'danger'" 
                                    :label="$user['status'] === 'active' ? 'Actif' : 'Inactif'" 
                                />
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm text-slate-300">{{ $user['last_login'] }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm text-slate-300">{{ $user['phone'] }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="text-cyan-400 hover:text-cyan-300 transition-colors" title="Voir">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-blue-400 hover:text-blue-300 transition-colors" title="Modifier">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-amber-400 hover:text-amber-300 transition-colors" title="Permissions">
                                        <i data-lucide="shield" class="w-4 h-4"></i>
                                    </button>
                                    @if($user['status'] === 'active')
                                    <button class="text-red-400 hover:text-red-300 transition-colors" title="Désactiver">
                                        <i data-lucide="user-x" class="w-4 h-4"></i>
                                    </button>
                                    @else
                                    <button class="text-emerald-400 hover:text-emerald-300 transition-colors" title="Activer">
                                        <i data-lucide="user-check" class="w-4 h-4"></i>
                                    </button>
=======
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $roleColors[$primaryRole] ?? 'cyan' }}-500/20 text-{{ $roleColors[$primaryRole] ?? 'cyan' }}-400 border border-{{ $roleColors[$primaryRole] ?? 'cyan' }}-500/30">
                                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                    {{ $roleLabels[$primaryRole] ?? ucfirst($primaryRole) }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($user->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactif
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm text-slate-300">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit User Button -->
                                    <button onclick="openEditUserModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $primaryRole, 'is_active' => (bool)$user->is_active, 'is_last_admin' => $isLastAdmin]) }})" class="p-1.5 rounded-lg text-blue-400 hover:bg-blue-500/20 transition-colors" title="Modifier l'utilisateur & rôle">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Toggle Status Form -->
                                    @if(!$isLastAdmin || !$user->is_active)
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" onsubmit="return confirm('Confirmer le changement de statut pour cet utilisateur ?');" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_active)
                                        <button type="submit" class="p-1.5 rounded-lg text-amber-400 hover:bg-amber-500/20 transition-colors" title="Désactiver le compte">
                                            <i data-lucide="user-x" class="w-4 h-4"></i>
                                        </button>
                                        @else
                                        <button type="submit" class="p-1.5 rounded-lg text-emerald-400 hover:bg-emerald-500/20 transition-colors" title="Activer le compte">
                                            <i data-lucide="user-check" class="w-4 h-4"></i>
                                        </button>
                                        @endif
                                    </form>
                                    @else
                                    <span class="p-1.5 rounded-lg text-slate-600 cursor-not-allowed" title="Désactivation impossible : dernier administrateur">
                                        <i data-lucide="user-x" class="w-4 h-4"></i>
                                    </span>
                                    @endif

                                    <!-- Delete User Form -->
                                    @if(!$isLastAdmin && auth()->id() !== $user->id)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:bg-red-500/20 transition-colors" title="Supprimer le compte">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="p-1.5 rounded-lg text-slate-600 cursor-not-allowed" title="Suppression protégée">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </span>
>>>>>>> user
                                    @endif
                                </div>
                            </td>
                        </tr>
<<<<<<< HEAD
                        @endforeach
=======
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                        @endforelse
>>>>>>> user
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
<<<<<<< HEAD
            <div class="mt-6 flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-sm text-slate-400">
                    Affichage de <span class="font-medium text-white">1-{{ count($users) }}</span> sur <span class="font-medium text-white">{{ count($users) }}</span> utilisateurs
                </div>
                <x-ui.pagination :current="1" :total="1" />
            </div>
        </x-ui.card>
    </div>
</div>
=======
            @if(method_exists($users, 'hasPages') && $users->hasPages())
            <div class="mt-6 border-t border-slate-700 pt-4">
                {{ $users->links() }}
            </div>
            @endif
        </x-ui.card>
    </div>
</div>

<!-- Modal Création Utilisateur -->
<div id="createUserModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5 text-cyan-400"></i>
                Nouvel Utilisateur AquaSecure
            </h3>
            <button onclick="closeCreateUserModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nom complet</label>
                <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Adresse email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Mot de passe provisoire (min. 8 car.)</label>
                <input type="password" name="password" required minlength="8" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Rôle officiel RBAC</label>
                <select name="role" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    <option value="administrateur">Administrateur (Accès complet & sécurité)</option>
                    <option value="manager">Gestionnaire (Supervision réseau & validation)</option>
                    <option value="technicien">Technicien (Saisie terrain des mesures)</option>
                    <option value="citoyen" selected>Citoyen (Consultation publique & signalements)</option>
                </select>
                <p class="text-xs text-slate-400 mt-1">Conforme aux 4 rôles officiels AquaSecure.</p>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded bg-slate-800 border-slate-700 text-cyan-500 focus:ring-0">
                <label for="create_is_active" class="text-sm text-slate-300">Compte actif immédiatement</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="closeCreateUserModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer l'utilisateur</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Édition Utilisateur -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="edit" class="w-5 h-5 text-blue-400"></i>
                Modifier l'Utilisateur
            </h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form id="editUserForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nom complet</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Adresse email</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nouveau mot de passe (laisser vide pour inchangé)</label>
                <input type="password" name="password" minlength="8" placeholder="••••••••" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Rôle officiel</label>
                <select id="edit_role" name="role" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    <option value="administrateur">Administrateur</option>
                    <option value="manager">Gestionnaire</option>
                    <option value="technicien">Technicien</option>
                    <option value="citoyen">Citoyen</option>
                </select>
                <p id="edit_admin_warning" class="hidden text-xs text-amber-400 mt-1">
                    ⚠️ Ce compte est le dernier administrateur actif. La rétrogradation est interdite.
                </p>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded bg-slate-800 border-slate-700 text-cyan-500 focus:ring-0">
                <label for="edit_is_active" class="text-sm text-slate-300">Compte actif</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="closeEditUserModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
    }
    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }

    function openEditUserModal(data) {
        const form = document.getElementById('editUserForm');
        form.action = `/admin/users/${data.id}`;
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_role').value = data.role;
        document.getElementById('edit_is_active').checked = data.is_active;

        const warning = document.getElementById('edit_admin_warning');
        if (data.is_last_admin) {
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
        }

        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }
</script>
>>>>>>> user
@endsection
