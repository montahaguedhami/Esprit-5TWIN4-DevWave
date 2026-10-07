@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="javascript:history.back()" class="p-2 rounded-lg glass hover:glass-strong transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-400"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="settings" class="w-8 h-8 text-blue-400"></i>
                    Paramètres
                </h1>
                <p class="text-slate-400">Personnalisez votre expérience AquaSecure</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Settings Menu -->
            <div class="lg:col-span-1">
                <x-ui.card>
                    <nav class="space-y-1">
                        @foreach([
                            ['id' => 'general', 'icon' => 'sliders', 'label' => 'Général', 'active' => true],
                            ['id' => 'notifications', 'icon' => 'bell', 'label' => 'Notifications', 'active' => false],
                            ['id' => 'security', 'icon' => 'shield', 'label' => 'Sécurité', 'active' => false],
                            ['id' => 'privacy', 'icon' => 'eye-off', 'label' => 'Confidentialité', 'active' => false],
                            ['id' => 'appearance', 'icon' => 'palette', 'label' => 'Apparence', 'active' => false],
                        ] as $item)
                        <button 
                            onclick="switchSettingsTab('{{ $item['id'] }}')"
                            data-settings-tab="{{ $item['id'] }}"
                            class="settings-tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ $item['active'] ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}"
                        >
                            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                            <span class="font-medium">{{ $item['label'] }}</span>
                        </button>
                        @endforeach
                    </nav>
                </x-ui.card>
            </div>

            <!-- Settings Content -->
            <div class="lg:col-span-3">
                <!-- General Settings -->
                <div id="settings-general" class="settings-content">
                    <x-ui.card class="mb-6">
                        <h3 class="text-xl font-bold text-white mb-6">Paramètres Généraux</h3>
                        
                        <div class="space-y-6">
                            <!-- Language -->
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-2">Langue</label>
                                <x-forms.select name="language">
                                    <option value="fr" selected>Français</option>
                                    <option value="ar">العربية</option>
                                    <option value="en">English</option>
                                </x-forms.select>
                                <p class="text-xs text-slate-500 mt-1">Choisissez votre langue préférée pour l'interface</p>
                            </div>

                            <!-- Timezone -->
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-2">Fuseau horaire</label>
                                <x-forms.select name="timezone">
                                    <option value="Africa/Tunis" selected>Africa/Tunis (GMT+1)</option>
                                    <option value="Europe/Paris">Europe/Paris (GMT+1)</option>
                                    <option value="UTC">UTC (GMT+0)</option>
                                </x-forms.select>
                            </div>

                            <!-- Date Format -->
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-2">Format de date</label>
                                <x-forms.select name="date_format">
                                    <option value="dd/mm/yyyy" selected>JJ/MM/AAAA (26/09/2026)</option>
                                    <option value="mm/dd/yyyy">MM/JJ/AAAA (09/26/2026)</option>
                                    <option value="yyyy-mm-dd">AAAA-MM-JJ (2026-09-26)</option>
                                </x-forms.select>
                            </div>

                            <!-- Save Button -->
                            <div class="pt-4 border-t border-slate-700">
                                <button class="btn btn-primary" onclick="showToast('Paramètres sauvegardés', 'success')">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Notifications Settings -->
                <div id="settings-notifications" class="settings-content hidden">
                    <x-ui.card class="mb-6">
                        <h3 class="text-xl font-bold text-white mb-6">Préférences de Notifications</h3>
                        
                        <div class="space-y-4">
                            @foreach([
                                ['label' => 'Notifications par email', 'desc' => 'Recevoir les alertes importantes par email', 'checked' => true],
                                ['label' => 'Notifications push', 'desc' => 'Recevoir des notifications sur votre navigateur', 'checked' => true],
                                ['label' => 'Alertes de qualité', 'desc' => 'Être notifié des problèmes de qualité de l\'eau', 'checked' => true],
                                ['label' => 'Maintenance programmée', 'desc' => 'Recevoir les informations sur les maintenances', 'checked' => true],
                                ['label' => 'Rapports mensuels', 'desc' => 'Recevoir un résumé mensuel par email', 'checked' => false],
                                ['label' => 'Newsletter', 'desc' => 'Recevoir les actualités et mises à jour', 'checked' => false],
                            ] as $notif)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-white">{{ $notif['label'] }}</div>
                                    <div class="text-xs text-slate-400 mt-1">{{ $notif['desc'] }}</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" {{ $notif['checked'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                                </label>
                            </div>
                            @endforeach

                            <div class="pt-4 border-t border-slate-700">
                                <button class="btn btn-primary" onclick="showToast('Préférences sauvegardées', 'success')">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Security Settings -->
                <div id="settings-security" class="settings-content hidden">
                    <x-ui.card class="mb-6">
                        <h3 class="text-xl font-bold text-white mb-6">Sécurité</h3>
                        
                        <div class="space-y-6">
                            <!-- Change Password -->
                            <div class="p-6 rounded-lg bg-slate-800/50">
                                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <i data-lucide="key" class="w-5 h-5 text-cyan-400"></i>
                                    Changer le mot de passe
                                </h4>
                                <div class="space-y-4">
                                    <x-forms.input 
                                        name="current_password" 
                                        type="password" 
                                        placeholder="Mot de passe actuel"
                                    />
                                    <x-forms.input 
                                        name="new_password" 
                                        type="password" 
                                        placeholder="Nouveau mot de passe"
                                    />
                                    <x-forms.input 
                                        name="confirm_password" 
                                        type="password" 
                                        placeholder="Confirmer le nouveau mot de passe"
                                    />
                                    <button class="btn btn-primary" onclick="showToast('Mot de passe modifié', 'success')">
                                        Changer le mot de passe
                                    </button>
                                </div>
                            </div>

                            <!-- Two-Factor Auth -->
                            <div class="p-6 rounded-lg bg-slate-800/50">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                                            <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i>
                                            Authentification à deux facteurs
                                        </h4>
                                        <p class="text-sm text-slate-400">Ajoutez une couche de sécurité supplémentaire</p>
                                    </div>
                                    <x-ui.status-badge status="warning" label="Désactivé" />
                                </div>
                                <button class="btn btn-secondary">
                                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                                    Activer 2FA
                                </button>
                            </div>

                            <!-- Active Sessions -->
                            <div class="p-6 rounded-lg bg-slate-800/50">
                                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <i data-lucide="monitor" class="w-5 h-5 text-blue-400"></i>
                                    Sessions actives
                                </h4>
                                <div class="space-y-3">
                                    @foreach([
                                        ['device' => 'Windows - Chrome', 'location' => 'Tunis, Tunisie', 'time' => 'Session actuelle', 'current' => true],
                                        ['device' => 'Android - Chrome', 'location' => 'Tunis, Tunisie', 'time' => 'Il y a 2 jours', 'current' => false],
                                    ] as $session)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/50">
                                        <div class="flex items-center gap-3">
                                            <i data-lucide="{{ $session['current'] ? 'monitor' : 'smartphone' }}" class="w-5 h-5 text-slate-400"></i>
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ $session['device'] }}</div>
                                                <div class="text-xs text-slate-400">{{ $session['location'] }} • {{ $session['time'] }}</div>
                                            </div>
                                        </div>
                                        @if(!$session['current'])
                                        <button class="text-red-400 hover:text-red-300 text-sm">Révoquer</button>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Privacy Settings -->
                <div id="settings-privacy" class="settings-content hidden">
                    <x-ui.card class="mb-6">
                        <h3 class="text-xl font-bold text-white mb-6">Confidentialité</h3>
                        
                        <div class="space-y-4">
                            @foreach([
                                ['label' => 'Profil public', 'desc' => 'Permettre aux autres utilisateurs de voir votre profil', 'checked' => false],
                                ['label' => 'Partager les données d\'utilisation', 'desc' => 'Aider à améliorer AquaSecure', 'checked' => true],
                                ['label' => 'Historique d\'activité', 'desc' => 'Conserver l\'historique de vos actions', 'checked' => true],
                            ] as $privacy)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-white">{{ $privacy['label'] }}</div>
                                    <div class="text-xs text-slate-400 mt-1">{{ $privacy['desc'] }}</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" {{ $privacy['checked'] ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                                </label>
                            </div>
                            @endforeach

                            <div class="pt-4 border-t border-slate-700">
                                <button class="btn btn-danger">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    Supprimer mon compte
                                </button>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Appearance Settings -->
                <div id="settings-appearance" class="settings-content hidden">
                    <x-ui.card class="mb-6">
                        <h3 class="text-xl font-bold text-white mb-6">Apparence</h3>
                        
                        <div class="space-y-6">
                            <!-- Theme -->
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-3">Thème</label>
                                <div class="grid grid-cols-3 gap-4">
                                    @foreach([
                                        ['id' => 'dark', 'label' => 'Sombre', 'active' => true],
                                        ['id' => 'light', 'label' => 'Clair', 'active' => false],
                                        ['id' => 'auto', 'label' => 'Auto', 'active' => false],
                                    ] as $theme)
                                    <button class="p-4 rounded-lg border-2 {{ $theme['active'] ? 'border-cyan-500 bg-cyan-500/10' : 'border-slate-700 bg-slate-800/50' }} hover:border-cyan-500 transition-all">
                                        <i data-lucide="{{ $theme['id'] === 'dark' ? 'moon' : ($theme['id'] === 'light' ? 'sun' : 'laptop') }}" class="w-6 h-6 mx-auto mb-2 {{ $theme['active'] ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                                        <div class="text-sm font-medium {{ $theme['active'] ? 'text-white' : 'text-slate-400' }}">{{ $theme['label'] }}</div>
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Density -->
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-3">Densité</label>
                                <x-forms.select name="density">
                                    <option value="comfortable" selected>Confortable</option>
                                    <option value="compact">Compacte</option>
                                    <option value="spacious">Spacieuse</option>
                                </x-forms.select>
                            </div>

                            <div class="pt-4 border-t border-slate-700">
                                <button class="btn btn-primary" onclick="showToast('Apparence sauvegardée', 'success')">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchSettingsTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.settings-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remove active state from all buttons
        document.querySelectorAll('.settings-tab-btn').forEach(btn => {
            btn.classList.remove('bg-cyan-500/20', 'text-cyan-400');
            btn.classList.add('text-slate-400');
        });
        
        // Show selected tab
        document.getElementById('settings-' + tabId).classList.remove('hidden');
        
        // Add active state to clicked button
        const activeBtn = document.querySelector('[data-settings-tab="' + tabId + '"]');
        activeBtn.classList.add('bg-cyan-500/20', 'text-cyan-400');
        activeBtn.classList.remove('text-slate-400');
        
        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
</script>
@endpush
@endsection
