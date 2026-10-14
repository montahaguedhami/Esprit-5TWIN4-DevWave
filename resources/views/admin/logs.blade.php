@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="file-text" class="w-8 h-8 text-blue-400"></i>
<<<<<<< HEAD
                    Logs Système
                </h1>
                <p class="text-slate-400">Historique des événements et activités du système</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="btn btn-secondary">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exporter
                </button>
                <button class="btn btn-secondary">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Purger anciens logs
                </button>
            </div>
        </div>

=======
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

>>>>>>> user
        <!-- Log Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-500/20 mb-2">
<<<<<<< HEAD
                    <i data-lucide="info" class="w-6 h-6 text-blue-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">8,247</div>
                <div class="text-sm text-slate-400">Info</div>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-amber-500/20 mb-2">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">142</div>
                <div class="text-sm text-slate-400">Warnings</div>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-red-500/20 mb-2">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">23</div>
                <div class="text-sm text-slate-400">Erreurs</div>
=======
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
>>>>>>> user
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-500/20 mb-2">
<<<<<<< HEAD
                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">99.7%</div>
                <div class="text-sm text-slate-400">Succès</div>
=======
                    <i data-lucide="droplet" class="w-6 h-6 text-emerald-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">{{ $measureLogs }}</div>
                <div class="text-sm text-slate-400">Mesures & Validations</div>
>>>>>>> user
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card class="mb-6">
<<<<<<< HEAD
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <x-ui.search-input placeholder="Rechercher dans les logs..." />
                </div>
                
                <x-forms.select name="level" class="w-40">
                    <option value="">Tous les niveaux</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="error">Erreur</option>
                </x-forms.select>

                <x-forms.select name="type" class="w-48">
                    <option value="">Tous les types</option>
                    <option value="user_login">Connexion</option>
                    <option value="incident_created">Incident</option>
                    <option value="system_alert">Alerte système</option>
                    <option value="failed_login">Échec connexion</option>
                </x-forms.select>

                <x-forms.select name="period" class="w-40">
                    <option value="today">Aujourd'hui</option>
                    <option value="week">7 derniers jours</option>
                    <option value="month">30 derniers jours</option>
                    <option value="all">Tous</option>
                </x-forms.select>

                <button class="btn btn-secondary">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>
        </x-ui.card>

        <!-- Logs List -->
        @php
            $logs = \App\Data\PlaceholderData::adminLogs();
        @endphp

        <x-ui.card>
            <div class="space-y-2">
                @foreach($logs as $log)
                <div class="p-4 rounded-lg bg-slate-800/50 hover:bg-slate-800 transition-colors cursor-pointer">
                    <div class="flex items-start gap-4">
                        <!-- Icon & Level -->
                        <div class="flex-shrink-0">
                            @php
                                $levelConfig = [
                                    'info' => ['color' => 'blue', 'icon' => 'info'],
                                    'warning' => ['color' => 'amber', 'icon' => 'alert-triangle'],
                                    'error' => ['color' => 'red', 'icon' => 'x-circle'],
                                ];
                                $config = $levelConfig[$log['level']] ?? $levelConfig['info'];
                            @endphp
                            <div class="w-10 h-10 rounded-lg bg-{{ $config['color'] }}-500/20 flex items-center justify-center">
                                <i data-lucide="{{ $config['icon'] }}" class="w-5 h-5 text-{{ $config['color'] }}-400"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div>
                                    <div class="font-medium text-white mb-1">{{ $log['action'] }}</div>
                                    <div class="flex items-center gap-3 text-sm text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="user" class="w-3 h-3"></i>
                                            {{ $log['user'] }}
                                        </span>
                                        @if($log['ip'])
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="globe" class="w-3 h-3"></i>
                                            {{ $log['ip'] }}
                                        </span>
                                        @endif
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            {{ $log['timestamp'] }}
                                        </span>
                                    </div>
                                </div>
                                <x-ui.status-badge 
                                    :status="$config['color']" 
                                    :label="strtoupper($log['level'])" 
                                />
                            </div>

                            <!-- Type Badge -->
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs px-2 py-1 rounded bg-slate-700 text-slate-300">
                                    {{ str_replace('_', ' ', $log['type']) }}
                                </span>
                                <button class="text-xs text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                                    <span>Voir détails</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </button>
=======
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
>>>>>>> user
                            </div>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-sm text-slate-400">
                    Affichage de <span class="font-medium text-white">1-{{ count($logs) }}</span> sur <span class="font-medium text-white">8,412</span> logs
                </div>
                <x-ui.pagination :current="1" :total="5" />
            </div>
        </x-ui.card>

        <!-- Real-time Log Stream (placeholder) -->
        <x-ui.card class="mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="radio" class="w-5 h-5 text-emerald-400"></i>
                    Flux en temps réel
                    <span class="flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-emerald-500/20 text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live
                    </span>
                </h3>
                <button class="text-sm text-slate-400 hover:text-white transition-colors">
                    <i data-lucide="pause" class="w-4 h-4 inline mr-1"></i>
                    Pause
                </button>
            </div>

            <div class="bg-slate-950 rounded-lg p-4 font-mono text-xs space-y-1 max-h-64 overflow-y-auto">
                <div class="text-slate-400">[2026-09-26 10:32:45] <span class="text-blue-400">INFO</span> User login successful: admin@aquasecure.tn</div>
                <div class="text-slate-400">[2026-09-26 10:32:12] <span class="text-blue-400">INFO</span> API request: GET /api/zones - 200 OK (45ms)</div>
                <div class="text-slate-400">[2026-09-26 10:31:58] <span class="text-amber-400">WARNING</span> High memory usage detected: 78%</div>
                <div class="text-slate-400">[2026-09-26 10:31:42] <span class="text-blue-400">INFO</span> Intervention INT-089 status updated</div>
                <div class="text-slate-400">[2026-09-26 10:31:30] <span class="text-blue-400">INFO</span> Database backup completed successfully</div>
            </div>
=======
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
>>>>>>> user
        </x-ui.card>
    </div>
</div>
@endsection
