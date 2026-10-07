@extends('layouts.app')

@section('title', 'Espace Technicien — AquaSecure')

@php
    use App\Data\PlaceholderData;
    $user          = session('user', ['name' => 'Amira Ben Ali', 'role' => 'technician']);
    $interventions = PlaceholderData::technicianInterventions();
    $equipment     = PlaceholderData::technicianEquipment();
    $zones         = PlaceholderData::technicianZones();

    $firstName     = explode(' ', $user['name'])[0];
    $today         = count($interventions);
    $inProgress    = count(array_filter($interventions, fn($i)=>$i['status']==='in_progress'));
    $scheduled     = count(array_filter($interventions, fn($i)=>$i['status']==='scheduled'));
    $completed     = count(array_filter($interventions, fn($i)=>$i['status']==='completed'));
    $urgent        = count(array_filter($interventions, fn($i)=>$i['priority']==='high'));

    $statusStyle = [
        'in_progress' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400 animate-pulse','label'=>'En cours'],
        'scheduled'   => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400',              'label'=>'Planifiée'],
        'completed'   => ['bg'=>'bg-teal-500/15', 'text'=>'text-teal-300', 'dot'=>'bg-teal-400',              'label'=>'Terminée'],
    ];
    $priorityStyle = [
        'high'   => ['bg'=>'bg-red-500/15',  'text'=>'text-red-300',  'label'=>'Urgent'],
        'medium' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'Moyen'],
        'low'    => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'label'=>'Faible'],
    ];
@endphp

@section('content')
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative">

{{-- ── TOPBAR TECHNICIEN (Liquid Glass with Back Arrow) ── --}}
<nav class="sticky top-0 z-50 glass-strong border-b border-white/20 px-4 sm:px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <x-back-button :fallback="route('landing')" />
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
            </div>
            <span class="font-display font-semibold text-white text-lg tracking-tight hidden sm:block">AquaSecure</span>
            <div class="liquid-chip">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                <span class="text-[11px] font-medium text-white/90">Espace Technicien</span>
            </div>
        </div>
    </div>

    {{-- Dedicated Technician Navigation Menu --}}
    <div class="hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl text-xs font-medium">
        <a href="{{ route('technician.dashboard') }}" class="px-3.5 py-1.5 rounded-xl text-white bg-white/15 transition-all">Tableau de bord</a>
        <a href="{{ route('technician.interventions.index') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Interventions</a>
        <a href="{{ route('technician.equipment') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Équipement & Matériel</a>
    </div>

    <div class="flex items-center gap-3">
        <x-notification-center />
        <x-user-menu />
    </div>
</nav>

<div class="max-w-[1550px] w-full mx-auto px-4 sm:px-8 py-8 space-y-6 animate-fade-in-up">

    {{-- ══ HEADER ══════════════════════════════════════════════ --}}
    <div class="glass rounded-2xl p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white">
                    Bonjour, {{ $firstName }} 👋
                </h1>
                <p class="text-cyan-100/55 text-sm mt-0.5">Technicien · Interventions terrain</p>
                <p class="text-cyan-100/65 text-sm mt-2">
                    Vous avez <strong class="text-white">{{ $today }}</strong> interventions
                    @if($inProgress > 0)
                        · <strong class="text-amber-300">{{ $inProgress }}</strong> en cours
                    @endif
                    @if($urgent > 0)
                        · <strong class="text-red-300">{{ $urgent }}</strong> urgente(s)
                    @endif
                </p>
            </div>
            <a href="{{ route('technician.interventions.index') }}"
               class="shrink-0 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold
                      flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Toutes les interventions</span>
            </a>
        </div>
    </div>

    {{-- ══ 4 KPI CARDS ════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['val'=>$today,    'label'=>'Aujourd\'hui',  'icon'=>'calendar',     'bg'=>'bg-cyan-500/10',  'ic'=>'text-cyan-400'],
            ['val'=>$inProgress,'label'=>'En cours',      'icon'=>'loader',        'bg'=>'bg-amber-500/10', 'ic'=>'text-amber-400'],
            ['val'=>$completed, 'label'=>'Terminées',     'icon'=>'check-circle',  'bg'=>'bg-teal-500/10',  'ic'=>'text-teal-400'],
            ['val'=>$urgent,    'label'=>'Urgentes',      'icon'=>'alert-triangle','bg'=>'bg-red-500/10',   'ic'=>'text-red-400'],
        ] as $kpi)
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl {{ $kpi['bg'] }} flex items-center justify-center mb-3">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-5 h-5 {{ $kpi['ic'] }}"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white">{{ $kpi['val'] }}</p>
            <p class="text-xs text-cyan-100/55 mt-0.5">{{ $kpi['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ══ URGENTE EN TÊTE (si existe) ═══════════════════════ --}}
    @php $urgentInt = array_values(array_filter($interventions, fn($i)=>$i['priority']==='high' && $i['status']==='in_progress'))[0] ?? null; @endphp
    @if($urgentInt)
    <div class="glass rounded-2xl p-5 border-red-500/30 ring-1 ring-red-500/20">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/15 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 animate-pulse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-mono font-bold text-red-400">{{ $urgentInt['id'] }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/15 text-red-300">URGENT</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300">En cours</span>
                    </div>
                    <p class="text-white font-bold">{{ $urgentInt['type'] }}</p>
                    <p class="text-cyan-100/55 text-xs mt-0.5">
                        <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
                        {{ $urgentInt['zone'] }} — {{ $urgentInt['address'] }}
                    </p>
                </div>
            </div>
            <a href="{{ route('technician.interventions.show', $urgentInt['id']) }}"
               class="shrink-0 bg-gradient-to-r from-red-500/20 to-red-600/20 border border-red-500/30
                      text-red-300 hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                Voir →
            </a>
        </div>
    </div>
    @endif

    {{-- ══ INTERVENTIONS DU JOUR ═══════════════════════════════ --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Mes interventions</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">{{ $today }} assignées · {{ $scheduled }} planifiées</p>
            </div>
            <a href="{{ route('technician.interventions.index') }}"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            @foreach($interventions as $int)
            @php
                $ss = $statusStyle[$int['status']] ?? $statusStyle['scheduled'];
                $ps = $priorityStyle[$int['priority']] ?? $priorityStyle['low'];
            @endphp
            <div class="flex items-center gap-3 px-5 py-4 hover:bg-white/[.025] transition-colors">
                {{-- Priority dot --}}
                <div class="w-9 h-9 rounded-xl {{ $ps['bg'] }} flex items-center justify-center shrink-0">
                    @if($int['priority']==='high')
                    <i data-lucide="alert-triangle" class="w-4 h-4 {{ $ps['text'] }}"></i>
                    @elseif($int['status']==='in_progress')
                    <i data-lucide="loader" class="w-4 h-4 {{ $ss['text'] }}"></i>
                    @else
                    <i data-lucide="wrench" class="w-4 h-4 text-cyan-400/70"></i>
                    @endif
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                        <span class="text-[10px] font-mono font-bold text-cyan-400">{{ $int['id'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $ps['bg'] }} {{ $ps['text'] }}">{{ $ps['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }}">{{ $ss['label'] }}</span>
                    </div>
                    <p class="text-white text-sm font-semibold truncate">{{ $int['type'] }}</p>
                    <p class="text-cyan-100/45 text-xs truncate">
                        <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
                        {{ $int['zone'] }} · {{ $int['scheduled_time'] }}
                    </p>
                </div>
                {{-- Actions --}}
                <div class="flex gap-1.5 shrink-0">
                    @if($int['status']==='scheduled')
                    <button onclick="showToast('Intervention démarrée', 'success')"
                            class="glass p-2 rounded-lg text-teal-400 hover:text-white transition-colors"
                            title="Démarrer">
                        <i data-lucide="play" class="w-3.5 h-3.5"></i>
                    </button>
                    @elseif($int['status']==='in_progress')
                    <a href="{{ route('technician.interventions.report', $int['id']) }}"
                       class="glass p-2 rounded-lg text-amber-400 hover:text-white transition-colors"
                       title="Rapport">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    </a>
                    @endif
                    <a href="{{ route('technician.interventions.show', $int['id']) }}"
                       class="glass p-2 rounded-lg text-cyan-400/60 hover:text-cyan-300 transition-colors"
                       title="Détails">
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══ ÉQUIPEMENT + ZONES côte à côte ═══════════════════ --}}
    <div class="grid sm:grid-cols-2 gap-6">

        {{-- Équipement --}}
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-white font-display font-bold text-sm">Mon équipement</h2>
                <a href="{{ route('technician.equipment') }}"
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Tout voir →
                </a>
            </div>
            <div class="divide-y divide-white/[.04]">
                @foreach(array_slice($equipment, 0, 5) as $eq)
                @php
                    $eqStyle = [
                        'available' => ['dot'=>'bg-teal-400','text'=>'text-teal-300','label'=>'OK'],
                        'in_use'    => ['dot'=>'bg-amber-400','text'=>'text-amber-300','label'=>'Utilisé'],
                        'low_stock' => ['dot'=>'bg-red-400 animate-pulse','text'=>'text-red-300','label'=>'Stock bas'],
                        'maintenance'=>['dot'=>'bg-blue-400','text'=>'text-blue-300','label'=>'Maintenance'],
                    ][$eq['status']] ?? ['dot'=>'bg-slate-400','text'=>'text-slate-300','label'=>$eq['status']];
                @endphp
                <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[.02] transition-colors">
                    <span class="w-2 h-2 rounded-full {{ $eqStyle['dot'] }} shrink-0"></span>
                    <span class="text-sm text-cyan-100/75 flex-1 truncate">{{ $eq['name'] }}</span>
                    <span class="text-xs font-semibold {{ $eqStyle['text'] }} shrink-0">{{ $eqStyle['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Zones assignées + Contact urgence --}}
        <div class="space-y-4">
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5">
                    <h2 class="text-white font-display font-bold text-sm">Mes zones</h2>
                </div>
                <div class="divide-y divide-white/[.04]">
                    @foreach($zones as $zone)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[.02] transition-colors">
                        <span class="text-lg shrink-0">{{ $zone['emoji'] }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-semibold">{{ $zone['name'] }}</p>
                            <p class="text-cyan-100/40 text-xs">Qualité de l'eau : {{ $zone['quality'] }}%</p>
                        </div>
                        <span class="w-2 h-2 rounded-full shrink-0
                            {{ $zone['status']==='normal' ? 'bg-teal-400' : ($zone['status']==='alert' ? 'bg-amber-400' : 'bg-red-400') }}">
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Contact urgence --}}
            <div class="glass rounded-2xl p-4 text-center">
                <p class="text-xs text-cyan-100/50 mb-3">Besoin d'aide ?</p>
                <button onclick="showToast('Appel dispatching en cours...', 'info')"
                        class="flex items-center justify-center gap-2 w-full bg-red-500/10 border border-red-500/25
                               text-red-300 hover:text-white hover:bg-red-500/20 text-sm font-semibold
                               py-2.5 rounded-xl transition-all">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                    Appeler le dispatching
                </button>
            </div>
        </div>
    </div>

    {{-- ══ HISTORIQUE RÉCENT ═══════════════════════════════════ --}}
    @if($completed > 0)
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-white font-display font-bold text-sm">Récemment terminées</h2>
        </div>
        <div class="divide-y divide-white/[.04]">
            @foreach(array_values(array_filter($interventions, fn($i)=>$i['status']==='completed')) as $int)
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.02] transition-colors">
                <div class="w-8 h-8 rounded-lg bg-teal-500/10 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4 text-teal-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">{{ $int['type'] }}</p>
                    <p class="text-cyan-100/40 text-[11px]">{{ $int['zone'] }}</p>
                </div>
                <span class="text-[10px] text-teal-300/70 font-semibold shrink-0">
                    {{ isset($int['completed_at']) ? $int['completed_at'] : 'Terminée' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /container --}}
</div>{{-- /min-h-screen --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
