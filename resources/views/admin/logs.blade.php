@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="file-text" class="w-8 h-8 text-blue-400"></i>
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

        <!-- Log Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-500/20 mb-2">
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
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-500/20 mb-2">
                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-400"></i>
                </div>
                <div class="text-2xl font-bold text-white">99.7%</div>
                <div class="text-sm text-slate-400">Succès</div>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card class="mb-6">
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
                            </div>
                        </div>
                    </div>
                </div>
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
        </x-ui.card>
    </div>
</div>
@endsection
