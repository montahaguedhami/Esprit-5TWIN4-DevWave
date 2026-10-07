@extends('layouts.admin')

@section('admin-content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="hard-drive" class="w-8 h-8 text-cyan-400"></i>
                    Sauvegardes
                </h1>
                <p class="text-slate-400">Gestion des sauvegardes automatiques et manuelles</p>
            </div>
            <button class="btn btn-primary">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                Nouvelle sauvegarde
            </button>
        </div>

        <!-- Backup Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.card>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-6 h-6 text-emerald-400"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">12</div>
                        <div class="text-sm text-slate-400">Sauvegardes</div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <i data-lucide="database" class="w-6 h-6 text-blue-400"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">28.4 GB</div>
                        <div class="text-sm text-slate-400">Stockage utilisé</div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <i data-lucide="clock" class="w-6 h-6 text-cyan-400"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">02:00</div>
                        <div class="text-sm text-slate-400">Prochaine sauvegarde</div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <i data-lucide="calendar" class="w-6 h-6 text-purple-400"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">30</div>
                        <div class="text-sm text-slate-400">Jours rétention</div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Backup Configuration -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <x-ui.card>
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="settings" class="w-5 h-5 text-cyan-400"></i>
                    Configuration
                </h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50">
                        <div>
                            <div class="text-sm font-medium text-white">Sauvegardes automatiques</div>
                            <div class="text-xs text-slate-400">Tous les jours à 02:00</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50">
                        <div>
                            <div class="text-sm font-medium text-white">Compression</div>
                            <div class="text-xs text-slate-400">Réduire la taille</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50">
                        <div>
                            <div class="text-sm font-medium text-white">Notifications</div>
                            <div class="text-xs text-slate-400">Alertes par email</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-700">
                        <button class="w-full btn btn-secondary text-sm">
                            <i data-lucide="sliders" class="w-4 h-4"></i>
                            Configuration avancée
                        </button>
                    </div>
                </div>
            </x-ui.card>

            <div class="lg:col-span-2">
                <x-ui.card>
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-purple-400"></i>
                        Stockage
                    </h3>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-slate-400">Utilisation du stockage</span>
                            <span class="text-sm font-medium text-white">28.4 GB / 100 GB</span>
                        </div>
                        <div class="w-full bg-slate-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-3 rounded-full transition-all duration-500" style="width: 28.4%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-xs text-slate-400">
                            <span>28.4% utilisé</span>
                            <span>71.6 GB disponible</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="database" class="w-4 h-4 text-cyan-400"></i>
                                <span class="text-sm text-slate-400">Base de données</span>
                            </div>
                            <div class="text-xl font-bold text-white">18.2 GB</div>
                            <div class="text-xs text-slate-400">64% du total</div>
                        </div>

                        <div class="p-4 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="folder" class="w-4 h-4 text-blue-400"></i>
                                <span class="text-sm text-slate-400">Fichiers</span>
                            </div>
                            <div class="text-xl font-bold text-white">10.2 GB</div>
                            <div class="text-xs text-slate-400">36% du total</div>
                        </div>
                    </div>

                    <div class="mt-6 p-4 rounded-lg bg-amber-500/10 border border-amber-500/20">
                        <div class="flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <div class="text-sm font-medium text-white mb-1">Attention</div>
                                <div class="text-xs text-slate-400">Nettoyage recommandé : Les sauvegardes de plus de 30 jours seront automatiquement supprimées.</div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <!-- Backups List -->
        @php
            $backups = \App\Data\PlaceholderData::adminBackups();
        @endphp

        <x-ui.card>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="archive" class="w-5 h-5 text-cyan-400"></i>
                    Historique des Sauvegardes
                </h3>
                <div class="flex items-center gap-2">
                    <x-forms.select name="filter" class="w-40">
                        <option value="all">Toutes</option>
                        <option value="automatic">Automatiques</option>
                        <option value="manual">Manuelles</option>
                    </x-forms.select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Nom</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Type</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Taille</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Statut</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Date</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Durée</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-slate-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="file" class="w-4 h-4 text-cyan-400"></i>
                                    <span class="text-sm font-mono text-white">{{ $backup['name'] }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <x-ui.status-badge 
                                    :status="$backup['type'] === 'automatic' ? 'info' : 'warning'" 
                                    :label="ucfirst($backup['type'])" 
                                />
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-sm text-slate-300">{{ $backup['size'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <x-ui.status-badge 
                                    :status="$backup['status'] === 'completed' ? 'success' : 'warning'" 
                                    :label="ucfirst($backup['status'])" 
                                />
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-sm text-slate-300">{{ $backup['created_at'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-sm text-slate-400">{{ $backup['duration'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="text-cyan-400 hover:text-cyan-300 transition-colors" title="Télécharger">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-blue-400 hover:text-blue-300 transition-colors" title="Restaurer">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-amber-400 hover:text-amber-300 transition-colors" title="Informations">
                                        <i data-lucide="info" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-red-400 hover:text-red-300 transition-colors" title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-sm text-slate-400">
                    Affichage de <span class="font-medium text-white">{{ count($backups) }}</span> sauvegardes
                </div>
                <button class="text-sm text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                    Charger plus
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
