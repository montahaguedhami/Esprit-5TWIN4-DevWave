@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="shield" class="w-8 h-8 text-purple-400"></i>
                    Rôles & Permissions
                </h1>
                <p class="text-slate-400">Gérer les rôles et contrôler l'accès aux fonctionnalités</p>
            </div>
            <button class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Nouveau Rôle
            </button>
        </div>

        @php
            $roles = \App\Data\PlaceholderData::adminRoles();
        @endphp

        <!-- Roles Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            @foreach($roles as $role)
            <x-ui.card>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-{{ $role['color'] }}-500/20 flex items-center justify-center">
                            <i data-lucide="shield" class="w-6 h-6 text-{{ $role['color'] }}-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $role['name'] }}</h3>
                            <p class="text-sm text-slate-400">{{ $role['description'] }}</p>
                        </div>
                    </div>
                    <x-ui.dropdown>
                        <x-slot name="trigger">
                            <button class="text-slate-400 hover:text-white transition-colors">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </x-slot>
                        <a href="#" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">
                            <i data-lucide="edit" class="w-4 h-4 inline mr-2"></i>
                            Modifier
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">
                            <i data-lucide="copy" class="w-4 h-4 inline mr-2"></i>
                            Dupliquer
                        </a>
                        @if($role['id'] !== 'admin')
                        <a href="#" class="block px-4 py-2 text-sm text-red-400 hover:bg-slate-700">
                            <i data-lucide="trash-2" class="w-4 h-4 inline mr-2"></i>
                            Supprimer
                        </a>
                        @endif
                    </x-ui.dropdown>
                </div>

                <div class="flex items-center gap-4 mb-4 pb-4 border-b border-slate-700">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ $role['users_count'] }}</div>
                        <div class="text-xs text-slate-400">Utilisateurs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ count($role['permissions']) }}</div>
                        <div class="text-xs text-slate-400">Modules</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400">Permissions</span>
                        <span class="text-cyan-400 cursor-pointer hover:text-cyan-300">Voir tout</span>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach(array_slice($role['permissions'], 0, 3) as $module => $perms)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-sm text-white capitalize">{{ $module }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                @foreach($perms as $perm)
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-700 text-slate-300">
                                    {{ substr($perm, 0, 1) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                        
                        @if(count($role['permissions']) > 3)
                        <div class="text-center">
                            <span class="text-xs text-slate-400">+{{ count($role['permissions']) - 3 }} modules supplémentaires</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700">
                    <button class="w-full btn btn-secondary text-sm">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Configurer les permissions
                    </button>
                </div>
            </x-ui.card>
            @endforeach
        </div>

        <!-- Permissions Matrix -->
        <x-ui.card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="grid" class="w-6 h-6 text-cyan-400"></i>
                    Matrice des Permissions
                </h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">C: Create | R: Read | U: Update | D: Delete</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Module</th>
                            @foreach($roles as $role)
                            <th class="text-center py-3 px-4 text-sm font-semibold text-{{ $role['color'] }}-400">
                                {{ $role['name'] }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allModules = ['users', 'roles', 'system', 'incidents', 'teams', 'reports', 'analytics', 'interventions', 'equipment', 'invoices', 'notifications'];
                        @endphp
                        
                        @foreach($allModules as $module)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="box" class="w-4 h-4 text-slate-400"></i>
                                    <span class="text-sm font-medium text-white capitalize">{{ $module }}</span>
                                </div>
                            </td>
                            @foreach($roles as $role)
                            <td class="py-3 px-4 text-center">
                                @if(isset($role['permissions'][$module]))
                                <div class="flex items-center justify-center gap-1">
                                    @foreach($role['permissions'][$module] as $perm)
                                    <span class="text-xs px-1.5 py-0.5 rounded bg-{{ $role['color'] }}-500/20 text-{{ $role['color'] }}-400 font-medium">
                                        {{ strtoupper(substr($perm, 0, 1)) }}
                                    </span>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-slate-600">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
