@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="file-text" class="w-8 h-8 text-blue-400"></i>
                    Journaux d'Audit & Sécurité
                </h1>
                <p class="text-slate-400">Traçabilité des opérations, modifications de rôles, permissions et activités sensibles</p>
            </div>
        </div>

        @php
            $realLogs = $logs ?? \App\Models\AuditLog::with('user')->latest()->paginate(20);
            $totalLogs = \App\Models\AuditLog::count();
            $roleLogs = \App\Models\AuditLog::where('action', 'like', '%role%')->count();
            $userLogs = \App\Models\AuditLog::where('action', 'like', '%user%')->count();
            $measureLogs = \App\Models\AuditLog::where('action', 'like', '%measure%')->count();
        @endphp

        <!-- Log Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-500/20 mb-2">
                    <i data-lucide="shield" class="w-6 h-6 text-blue-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">{{ $totalLogs }}</div>
                <div class="text-sm text-slate-400">Total Événements Audit</div>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-purple-500/20 mb-2">
                    <i data-lucide="key" class="w-6 h-6 text-purple-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">{{ $roleLogs }}</div>
                <div class="text-sm text-slate-400">Rôles & Permissions</div>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-cyan-500/20 mb-2">
                    <i data-lucide="users" class="w-6 h-6 text-cyan-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">{{ $userLogs }}</div>
                <div class="text-sm text-slate-400">Gestion Utilisateurs</div>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-500/20 mb-2">
                    <i data-lucide="droplet" class="w-6 h-6 text-emerald-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">{{ $measureLogs }}</div>
                <div class="text-sm text-slate-400">Mesures & Validations</div>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card class="mb-6">
            <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par email, action, IP..." class="w-full pl-9 pr-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    </div>
                </div>
                
                <select name="action" class="w-56 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-400 focus:outline-none">
                    <option value="">Toutes les actions</option>
                    <option value="user_login" {{ request('action') === 'user_login' ? 'selected' : '' }}>Connexion utilisateur</option>
                    <option value="user_created" {{ request('action') === 'user_created' ? 'selected' : '' }}>Création utilisateur</option>
                    <option value="user_updated" {{ request('action') === 'user_updated' ? 'selected' : '' }}>Modification utilisateur</option>
                    <option value="user_status_toggled" {{ request('action') === 'user_status_toggled' ? 'selected' : '' }}>Activation / Désactivation</option>
                    <option value="user_deleted" {{ request('action') === 'user_deleted' ? 'selected' : '' }}>Suppression utilisateur</option>
                    <option value="role_assigned" {{ request('action') === 'role_assigned' ? 'selected' : '' }}>Attribution de rôle</option>
                    <option value="role_permissions_updated" {{ request('action') === 'role_permissions_updated' ? 'selected' : '' }}>Mise à jour permissions</option>
                    <option value="measurement_created" {{ request('action') === 'measurement_created' ? 'selected' : '' }}>Saisie mesure</option>
                    <option value="measurement_validated" {{ request('action') === 'measurement_validated' ? 'selected' : '' }}>Validation mesure</option>
                </select>

                <button type="submit" class="btn btn-secondary text-sm">
                    <i data-lucide="filter" class="w-4 h-4"></i> Filtrer
                </button>
                @if(request()->hasAny(['search', 'action']))
                <a href="{{ route('admin.logs') }}" class="text-sm text-slate-400 hover:text-white">Réinitialiser</a>
                @endif
            </form>
        </x-ui.card>

        <!-- Logs List -->
        <x-ui.card>
            <div class="space-y-2">
                @forelse($realLogs as $log)
                @php
                    $isRoleOrSecurity = str_contains($log->action, 'role') || str_contains($log->action, 'delete') || str_contains($log->action, 'status');
                    $color = $isRoleOrSecurity ? 'purple' : (str_contains($log->action, 'validate') ? 'emerald' : 'blue');
                    $icon = $isRoleOrSecurity ? 'shield-alert' : (str_contains($log->action, 'login') ? 'log-in' : 'file-text');
                @endphp
                <div class="p-4 rounded-lg bg-slate-800/50 hover:bg-slate-800 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-{{ $color }}-500/20 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $icon }}" class="w-5 h-5 text-{{ $color }}-400"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-white text-sm font-mono">{{ $log->action }}</span>
                                    @if($log->resource_type)
                                    <span class="text-xs px-2 py-0.5 rounded bg-slate-700 text-slate-300">
                                        {{ $log->resource_type }} #{{ $log->resource_id ?? 'N/A' }}
                                    </span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap">{{ $log->created_at?->diffForHumans() ?? $log->created_at }}</span>
                            </div>

                            <p class="text-sm text-slate-300 mb-2">
                                Opération exécutée par : <strong class="text-cyan-300">{{ $log->user_email ?? 'Système' }}</strong>
                            </p>

                            @if(!empty($log->details))
                            <div class="p-2 rounded bg-slate-900/60 font-mono text-xs text-slate-400 overflow-x-auto">
                                {{ json_encode($log->details, JSON_UNESCAPED_UNICODE) }}
                            </div>
                            @endif

                            <div class="flex items-center gap-4 mt-2 text-xs text-slate-400">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                    IP : {{ $log->ip_address }}
                                </span>
                                <span>{{ $log->created_at?->format('d/m/Y H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400">
                    Aucun événement d'audit enregistré.
                </div>
                @endforelse
            </div>

            @if(method_exists($realLogs, 'hasPages') && $realLogs->hasPages())
            <div class="mt-6 border-t border-slate-700 pt-4">
                {{ $realLogs->links() }}
            </div>
            @endif
        </x-ui.card>
    </div>
</div>
@endsection
