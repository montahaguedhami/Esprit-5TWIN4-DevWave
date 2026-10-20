@extends('layouts.app')

@section('content')
<style>
    .aqua-light-page { min-height: 100vh; background: #f3f7fb !important; color: #102a43; }
    .aqua-light-page .glass, .aqua-light-page .glass-strong { background: #fff; border: 1px solid #dce7ef; box-shadow: 0 8px 24px rgba(15,42,67,.06); backdrop-filter: none; }
    .aqua-light-page [class~="text-white"] { color: #102a43 !important; }
    .aqua-light-page [class~="text-slate-300"], .aqua-light-page [class~="text-slate-400"], .aqua-light-page [class~="text-slate-500"] { color: #64748b !important; }
    .aqua-light-page [class*="text-cyan-100/"] { color: #64748b !important; }
    .aqua-light-page [class*="bg-slate-800"], .aqua-light-page [class*="bg-slate-900"], .aqua-light-page [class*="bg-slate-950"] { background-color: #f1f6fa !important; }
    .aqua-light-page [class*="border-slate-7"], .aqua-light-page [class*="border-slate-8"] { border-color: #e1eaf1 !important; }
    .aqua-light-page input:not([type="checkbox"]), .aqua-light-page select { color: #102a43; background-color: #fff; border-color: #cbd9e5; }
    .aqua-light-page .btn-primary { color: #fff !important; }
    @media (max-width: 640px) {
        .aqua-light-page .container { padding-left: 1rem; padding-right: 1rem; }
        .aqua-light-page .notification-item { padding: 1rem; }
    }
</style>
<div class="aqua-light-page min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="bell" class="w-8 h-8 text-purple-400"></i>
                    Toutes les notifications
                </h1>
                <p class="text-slate-400">Centre de notifications AquaSecure</p>
            </div>
            @if($nonLues > 0)
                <form method="POST" action="{{ route('notifications.tout-lire') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-white mb-1">{{ $total }}</div>
                <div class="text-sm text-slate-400">Total</div>
            </x-ui.card>
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-cyan-600 mb-1">{{ $nonLues }}</div>
                <div class="text-sm text-slate-400">Non lues</div>
            </x-ui.card>
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-emerald-600 mb-1">{{ $total - $nonLues }}</div>
                <div class="text-sm text-slate-400">Lues</div>
            </x-ui.card>
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-purple-600 mb-1">{{ $aujourdhui }}</div>
                <div class="text-sm text-slate-400">Aujourd'hui</div>
            </x-ui.card>
        </div>

        <!-- Filtres -->
        <div class="flex gap-2 mb-4">
            @foreach([null => 'Toutes', 'non-lues' => 'Non lues', 'lues' => 'Lues'] as $valeur => $libelle)
                <a href="{{ route('notifications.index', array_filter(['filtre' => $valeur])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-medium border transition-colors {{ $filtre === ($valeur ?: null) ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white text-slate-600 border-slate-200 hover:border-cyan-400' }}">
                    {{ $libelle }}
                </a>
            @endforeach
        </div>

        @php
            $couleurs = [
                'cyan' => 'bg-cyan-500/15 text-cyan-600',
                'amber' => 'bg-amber-500/15 text-amber-600',
                'rose' => 'bg-rose-500/15 text-rose-600',
                'emerald' => 'bg-emerald-500/15 text-emerald-600',
            ];
        @endphp

        <!-- Liste -->
        <x-ui.card padding="none" class="overflow-hidden">
            @forelse($notifications as $notification)
                <a href="{{ route('notifications.ouvrir', $notification->id) }}"
                   class="notification-item flex items-start gap-4 p-5 border-b border-slate-100 hover:bg-slate-50 transition-colors {{ $notification->read_at ? 'border-l-4 border-l-transparent' : 'bg-cyan-500/5 border-l-4 border-l-cyan-500' }}">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center {{ $couleurs[$notification->data['couleur'] ?? 'cyan'] ?? $couleurs['cyan'] }}">
                        <i data-lucide="{{ $notification->data['icone'] ?? 'bell' }}" class="w-6 h-6"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-base font-semibold text-white">{{ $notification->data['titre'] ?? 'Notification' }}</h3>
                            @unless($notification->read_at)
                                <span class="w-2 h-2 rounded-full bg-cyan-400 flex-shrink-0"></span>
                            @endunless
                        </div>
                        <p class="text-sm text-cyan-100/70 mb-2">{{ $notification->data['message'] ?? '' }}</p>
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            {{ $notification->created_at->locale('fr')->diffForHumans() }} · {{ $notification->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @if(! empty($notification->data['url']))
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 mt-1"></i>
                    @endif
                </a>
            @empty
                <x-ui.empty-state
                    icon="bell-off"
                    title="Aucune notification"
                    description="Vous êtes à jour ! Aucune notification pour le moment."
                />
            @endforelse
        </x-ui.card>

        <div class="mt-6">
            <x-maintenance.pagination :paginator="$notifications" />
        </div>
    </div>
</div>
@endsection
