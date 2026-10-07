@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="users" class="w-8 h-8 text-cyan-400"></i>
                    Gestion des Utilisateurs
                </h1>
                <p class="text-slate-400">Gérer les comptes et permissions du système</p>
            </div>
            <button class="btn btn-primary">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Nouvel Utilisateur
            </button>
        </div>

        <!-- Stats Cards -->
        @php
            $userStats = \App\Data\PlaceholderData::adminUserStats();
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <x-dashboard.kpi-card 
                icon="users" 
                label="Total Utilisateurs" 
                :value="$userStats['total']"
                iconColor="text-cyan-400"
            />
            <x-dashboard.kpi-card 
                icon="user-check" 
                label="Actifs" 
                :value="$userStats['active']"
                iconColor="text-emerald-400"
            />
            <x-dashboard.kpi-card 
                icon="shield" 
                label="Administrateurs" 
                :value="$userStats['admins']"
                iconColor="text-purple-400"
            />
            <x-dashboard.kpi-card 
                icon="briefcase" 
                label="Gestionnaires" 
                :value="$userStats['managers']"
                iconColor="text-blue-400"
            />
            <x-dashboard.kpi-card 
                icon="wrench" 
                label="Techniciens" 
                :value="$userStats['technicians']"
                iconColor="text-cyan-400"
            />
        </div>

        <!-- Filters -->
        <x-ui.card class="mb-6">
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

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Utilisateur</th>
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
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
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
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-sm text-slate-400">
                    Affichage de <span class="font-medium text-white">1-{{ count($users) }}</span> sur <span class="font-medium text-white">{{ count($users) }}</span> utilisateurs
                </div>
                <x-ui.pagination :current="1" :total="1" />
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
