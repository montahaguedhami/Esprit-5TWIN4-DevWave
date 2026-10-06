@extends('layouts.manager')

@php
    $title = 'Rapports — AquaSecure';
@endphp

@section('manager-content')
<div class="space-y-6 animate-fade-in-up">
    <div>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Centre de rapports</h1>
        <p class="text-cyan-100/55 text-sm mt-0.5">Exports opérationnels (démo)</p>
    </div>
    <div class="grid sm:grid-cols-3 gap-4">
        @foreach([
            ['icon'=>'alert-triangle','title'=>'Incidents du mois','desc'=>'Liste consolidée des signalements'],
            ['icon'=>'users','title'=>'Charge des équipes','desc'=>'Heures et interventions par technicien'],
            ['icon'=>'briefcase','title'=>'Suivi projets','desc'=>'Avancement et consommation budgétaire'],
        ] as $r)
        <button type="button" onclick="showToast('Génération du rapport (démo)', 'success')"
                class="glass rounded-2xl p-5 text-left hover-lift">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center mb-3">
                <i data-lucide="{{ $r['icon'] }}" class="w-5 h-5 text-cyan-400"></i>
            </div>
            <p class="text-white font-semibold">{{ $r['title'] }}</p>
            <p class="text-cyan-100/45 text-xs mt-1">{{ $r['desc'] }}</p>
        </button>
        @endforeach
    </div>
</div>
@endsection
