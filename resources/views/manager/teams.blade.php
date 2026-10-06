@extends('layouts.manager')

@php
    use App\Data\PlaceholderData;
    $title = 'Équipes — AquaSecure';
    $technicians = PlaceholderData::adminTechnicians();
    $teams       = PlaceholderData::analyticsTeamPerformance();
    $managers    = PlaceholderData::adminManagers();

    $onMission = count(array_filter($technicians, fn($t) => $t['status'] === 'on_mission'));
    $avail     = count(array_filter($technicians, fn($t) => $t['status'] === 'available'));
    $offDuty   = count(array_filter($technicians, fn($t) => $t['status'] === 'off_duty'));

    $techStatus = [
        'on_mission' => ['dot'=>'bg-amber-400 animate-pulse','bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En mission'],
        'available'  => ['dot'=>'bg-teal-400','bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Disponible'],
        'off_duty'   => ['dot'=>'bg-slate-500','bg'=>'bg-white/5','text'=>'text-slate-400','label'=>'Hors service'],
    ];
@endphp

@section('manager-content')
<div class="space-y-6 animate-fade-in-up">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Équipes terrain</h1>
            <p class="text-cyan-100/55 text-sm mt-0.5">Disponibilité, charge et performance des équipes</p>
        </div>
        <button type="button" onclick="showToast('Planification d\'équipe à implémenter', 'info')"
                class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Planifier une équipe
        </button>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['val'=>count($technicians),'label'=>'Techniciens','icon'=>'wrench','bg'=>'bg-cyan-500/10','ic'=>'text-cyan-400'],
            ['val'=>$onMission,'label'=>'En mission','icon'=>'navigation','bg'=>'bg-amber-500/10','ic'=>'text-amber-400'],
            ['val'=>$avail,'label'=>'Disponibles','icon'=>'check-circle','bg'=>'bg-teal-500/10','ic'=>'text-teal-400'],
            ['val'=>$offDuty,'label'=>'Hors service','icon'=>'moon','bg'=>'bg-slate-500/10','ic'=>'text-slate-300'],
        ] as $kpi)
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl {{ $kpi['bg'] }} flex items-center justify-center mb-3">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-5 h-5 {{ $kpi['ic'] }}"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white">{{ $kpi['val'] }}</p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">{{ $kpi['label'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3 glass rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5">
                <h2 class="text-white font-display font-bold">Techniciens</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">Affectation et statut en temps réel</p>
            </div>
            <div class="divide-y divide-white/[.04]">
                @foreach($technicians as $tech)
                @php $ts = $techStatus[$tech['status']]; @endphp
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.025] transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/15 flex items-center justify-center text-xs font-bold text-cyan-300 shrink-0">
                        {{ $tech['initials'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-semibold truncate">{{ $tech['name'] }}</p>
                        <p class="text-cyan-100/40 text-xs truncate">{{ $tech['zone'] }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ts['bg'] }} {{ $ts['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $ts['dot'] }}"></span>
                        {{ $ts['label'] }}
                    </span>
                    <span class="text-[11px] text-cyan-100/40 w-16 text-right">{{ $tech['interventions'] }} miss.</span>
                    <button type="button"
                            onclick="showToast('Mission envoyée à {{ $tech['name'] }} (démo)', 'success')"
                            class="glass px-2.5 py-1.5 rounded-lg text-[11px] text-cyan-300 hover:text-white font-semibold shrink-0
                                   {{ $tech['status'] === 'available' ? '' : 'opacity-40 pointer-events-none' }}">
                        Assigner
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5">
                    <h2 class="text-white font-display font-bold">Performance</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">Score de résolution 30 jours</p>
                </div>
                <div class="p-5 space-y-4">
                    @foreach($teams as $team)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-semibold text-white">{{ $team['team'] }}</span>
                            <span class="text-xs font-bold" style="color: {{ $team['color'] }}">{{ $team['score'] }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $team['score'] }}%; background: {{ $team['color'] }}"></div>
                        </div>
                        <p class="text-[10px] text-cyan-100/35 mt-1">
                            {{ $team['resolved'] }}/{{ $team['interventions'] }} résolus · {{ $team['avg_time'] }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5">
                    <h2 class="text-white font-display font-bold">Supervision</h2>
                </div>
                <div class="divide-y divide-white/[.04]">
                    @foreach($managers as $mgr)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/15 flex items-center justify-center text-[11px] font-bold text-blue-300">
                            {{ $mgr['initials'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-xs font-semibold truncate">{{ $mgr['name'] }}</p>
                            <p class="text-cyan-100/40 text-[11px]">{{ $mgr['zone'] }}</p>
                        </div>
                        <span class="text-[10px] {{ $mgr['status'] === 'active' ? 'text-teal-300' : 'text-slate-400' }}">
                            {{ $mgr['status'] === 'active' ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
