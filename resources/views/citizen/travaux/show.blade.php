@extends('layouts.frontoffice')

@section('title', 'Détail des travaux')

@section('frontoffice-content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('citizen.travaux.index') }}" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour aux travaux">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Intervention du {{ $intervention->date->format('d/m/Y') }}</h1>
            <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">Équipe {{ $intervention->technicien->specialite }}</p>
        </div>
    </div>

    <x-ui.card>
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="font-display font-semibold text-white">Nature des travaux</h2>
            <x-maintenance.badge :value="$intervention->statut" />
        </div>
        <p class="text-cyan-100/80 leading-relaxed whitespace-pre-line">{{ $intervention->description }}</p>

        <dl class="grid sm:grid-cols-2 gap-4 mt-6 pt-6 border-t border-white/10">
            <div class="flex items-center gap-3">
                <i data-lucide="calendar" class="w-5 h-5 text-cyan-300"></i>
                <div>
                    <dt class="text-xs text-cyan-100/50">Date</dt>
                    <dd class="text-white">{{ $intervention->date->locale('fr')->translatedFormat('l j F Y') }}</dd>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <i data-lucide="hard-hat" class="w-5 h-5 text-cyan-300"></i>
                <div>
                    <dt class="text-xs text-cyan-100/50">Équipe en charge</dt>
                    <dd class="text-white">{{ $intervention->technicien->specialite }}</dd>
                </div>
            </div>
        </dl>
    </x-ui.card>

    @if($intervention->statut !== 'Terminée')
        <x-ui.alert type="info" title="Un problème dans votre quartier ?">
            Si vous constatez une fuite ou une coupure prolongée, vous pouvez la signaler.
            <a href="{{ route('citizen.reports.create') }}" class="text-cyan-300 font-semibold hover:underline">Signaler un problème</a>
        </x-ui.alert>
    @endif
</div>
@endsection
