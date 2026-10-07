@extends('layouts.manager')

@section('title', 'Intervention #'.$intervention->id.' — AquaSecure')

@section('manager-content')
<div class="max-w-4xl mx-auto">
    <x-maintenance.page-header :title="'Intervention #'.$intervention->id" :subtitle="$intervention->technicien->nom" icon="clipboard-list">
        <x-maintenance.link-button :href="route('manager.interventions.index')" variant="secondary" icon="arrow-left">Retour</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.interventions.edit', $intervention)" variant="secondary" icon="pencil">Modifier</x-maintenance.link-button>
        <x-maintenance.delete-button :action="route('manager.interventions.destroy', $intervention)" confirm="Supprimer cette intervention ?" />
    </x-maintenance.page-header>

    @include('manager.maintenance._flash')

    <div class="grid md:grid-cols-3 gap-4">
        <x-ui.card class="md:col-span-2">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="font-display font-semibold text-white">Détails</h2>
                <x-maintenance.badge :value="$intervention->statut" />
            </div>
            <p class="text-cyan-100/80 leading-relaxed whitespace-pre-line">{{ $intervention->description }}</p>

            <dl class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-white/10">
                <div>
                    <dt class="text-xs font-semibold text-cyan-100/50">Date</dt>
                    <dd class="text-white mt-1">{{ $intervention->date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-cyan-100/50">Coût</dt>
                    <dd class="text-white mt-1">{{ number_format($intervention->cout, 2, ',', ' ') }} DT</dd>
                </div>
            </dl>
        </x-ui.card>

        {{-- Technicien associé (relation N:1) --}}
        <x-ui.card>
            <h2 class="font-display font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="hard-hat" class="w-5 h-5 text-cyan-300"></i> Technicien
            </h2>
            <a href="{{ route('manager.techniciens.show', $intervention->technicien) }}" class="text-lg font-semibold text-white hover:text-cyan-300">{{ $intervention->technicien->nom }}</a>
            <p class="text-sm text-cyan-100/60 mt-1">{{ $intervention->technicien->specialite }}</p>
            <p class="text-sm text-cyan-100/80 mt-3 flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-cyan-300"></i>{{ $intervention->technicien->telephone }}</p>
            <div class="mt-3"><x-maintenance.badge :value="$intervention->technicien->disponibilite" /></div>
        </x-ui.card>
    </div>
</div>
@endsection
