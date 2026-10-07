@extends('layouts.frontoffice')

@section('content')
<div class="space-y-6">
        @php
            $user = session('user', ['name' => 'Utilisateur', 'email' => 'user@aquasecure.tn', 'role' => 'citizen']);
            $roleLabels = [
                'admin' => 'Administrateur',
                'manager' => 'Gestionnaire',
                'technician' => 'Technicien',
                'citizen' => 'Citoyen',
            ];
        @endphp

        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="javascript:history.back()" class="p-2 rounded-lg glass hover:glass-strong transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-400"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="user-circle" class="w-8 h-8 text-cyan-400"></i>
                    Mon Profil
                </h1>
                <p class="text-slate-400">Gérer vos informations personnelles</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <x-ui.card>
                    <div class="text-center">
                        <!-- Avatar -->
                        <div class="relative inline-block mb-4">
                            <x-ui.avatar :name="$user['name']" size="2xl" />
                            <button class="absolute bottom-0 right-0 w-10 h-10 rounded-full bg-cyan-500 hover:bg-cyan-600 transition-colors flex items-center justify-center shadow-lg">
                                <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                            </button>
                        </div>

                        <!-- User Info -->
                        <h2 class="text-xl font-bold text-white mb-1">{{ $user['name'] }}</h2>
                        <p class="text-sm text-slate-400 mb-3">{{ $user['email'] }}</p>
                        
                        <x-ui.status-badge 
                            :status="$user['role'] === 'admin' ? 'purple' : ($user['role'] === 'manager' ? 'blue' : ($user['role'] === 'technician' ? 'cyan' : 'emerald'))" 
                            :label="$roleLabels[$user['role']] ?? 'Utilisateur'" 
                        />

                        <!-- Stats -->
                        <div class="mt-6 pt-6 border-t border-slate-700 grid grid-cols-3 gap-4">
                            <div>
                                <div class="text-2xl font-bold text-white">42</div>
                                <div class="text-xs text-slate-400">Actions</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-cyan-400">15</div>
                                <div class="text-xs text-slate-400">En cours</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-emerald-400">27</div>
                                <div class="text-xs text-slate-400">Terminés</div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Quick Actions -->
                <x-ui.card class="mt-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="zap" class="w-5 h-5 text-cyan-400"></i>
                        Actions Rapides
                    </h3>
                    <div class="space-y-2">
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="key" class="w-4 h-4"></i>
                            Changer le mot de passe
                        </button>
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            Préférences notifications
                        </button>
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            Sécurité et confidentialité
                        </button>
                    </div>
                </x-ui.card>
            </div>

            <!-- Profile Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <x-ui.card>
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <i data-lucide="user" class="w-6 h-6 text-cyan-400"></i>
                            Informations Personnelles
                        </h3>
                        <button class="btn btn-secondary btn-sm">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                            Modifier
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">Nom complet</label>
                            <x-forms.input 
                                name="name" 
                                :value="$user['name']" 
                                readonly 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">Email</label>
                            <x-forms.input 
                                name="email" 
                                type="email"
                                :value="$user['email']" 
                                readonly 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">Téléphone</label>
                            <x-forms.input 
                                name="phone" 
                                value="+216 98 765 432" 
                                readonly 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">Rôle</label>
                            <x-forms.input 
                                name="role" 
                                :value="$roleLabels[$user['role']] ?? 'Utilisateur'" 
                                readonly 
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Adresse</label>
                            <x-forms.input 
                                name="address" 
                                value="42 Avenue Habib Bourguiba, Tunis 1000" 
                                readonly 
                            />
                        </div>
                    </div>
                </x-ui.card>

                <!-- Account Settings -->
                <x-ui.card>
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i data-lucide="settings" class="w-6 h-6 text-blue-400"></i>
                        Paramètres du Compte
                    </h3>

                    <div class="space-y-4">
                        <!-- Language -->
                        <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                    <i data-lucide="globe" class="w-5 h-5 text-cyan-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">Langue</div>
                                    <div class="text-xs text-slate-400">Français</div>
                                </div>
                            </div>
                            <button class="text-cyan-400 hover:text-cyan-300 text-sm">Changer</button>
                        </div>

                        <!-- Timezone -->
                        <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                    <i data-lucide="clock" class="w-5 h-5 text-blue-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">Fuseau horaire</div>
                                    <div class="text-xs text-slate-400">Africa/Tunis (GMT+1)</div>
                                </div>
                            </div>
                            <button class="text-cyan-400 hover:text-cyan-300 text-sm">Changer</button>
                        </div>

                        <!-- Email Notifications -->
                        <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                    <i data-lucide="mail" class="w-5 h-5 text-purple-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">Notifications par email</div>
                                    <div class="text-xs text-slate-400">Recevoir les alertes importantes</div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                            </label>
                        </div>

                        <!-- Two-Factor Auth -->
                        <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">Authentification à deux facteurs</div>
                                    <div class="text-xs text-slate-400">Sécurité renforcée</div>
                                </div>
                            </div>
                            <button class="text-cyan-400 hover:text-cyan-300 text-sm">Activer</button>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Activity Log -->
                <x-ui.card>
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i data-lucide="activity" class="w-6 h-6 text-emerald-400"></i>
                        Activité Récente
                    </h3>

                    <div class="space-y-3">
                        @foreach([
                            ['action' => 'Connexion réussie', 'time' => 'Il y a 2h', 'icon' => 'log-in', 'color' => 'emerald'],
                            ['action' => 'Modification du profil', 'time' => 'Il y a 1 jour', 'icon' => 'edit', 'color' => 'blue'],
                            ['action' => 'Changement de mot de passe', 'time' => 'Il y a 3 jours', 'icon' => 'key', 'color' => 'purple'],
                            ['action' => 'Nouvelle déclaration créée', 'time' => 'Il y a 5 jours', 'icon' => 'file-plus', 'color' => 'cyan'],
                        ] as $activity)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-800/50">
                            <div class="w-8 h-8 rounded-lg bg-{{ $activity['color'] }}-500/20 flex items-center justify-center">
                                <i data-lucide="{{ $activity['icon'] }}" class="w-4 h-4 text-{{ $activity['color'] }}-400"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-white">{{ $activity['action'] }}</div>
                                <div class="text-xs text-slate-400">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>
        </div>
</div>
@endsection
