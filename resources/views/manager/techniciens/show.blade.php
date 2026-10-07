@extends('layouts.manager')

@section('title', $technicien->nom.' — AquaSecure')

@section('manager-content')
<div class="max-w-6xl mx-auto">
    <x-maintenance.page-header :title="$technicien->nom" :subtitle="$technicien->specialite" icon="hard-hat">
        <x-maintenance.link-button :href="route('manager.techniciens.index')" variant="secondary" icon="arrow-left">Retour</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.techniciens.edit', $technicien)" variant="secondary" icon="pencil">Modifier</x-maintenance.link-button>
        <x-maintenance.delete-button :action="route('manager.techniciens.destroy', $technicien)"
            confirm="Supprimer ce technicien ? Ses interventions seront aussi supprimées." />
    </x-maintenance.page-header>

    @include('manager.maintenance._flash')

    {{-- Informations + statistiques --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-2">Disponibilité</p>
            <x-maintenance.badge :value="$technicien->disponibilite" />
            <p class="text-sm text-cyan-100/80 mt-3 flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-cyan-300"></i>{{ $technicien->telephone }}</p>
        </x-ui.card>
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Interventions</p>
            <p class="text-3xl font-display font-bold text-white">{{ $interventions->count() }}</p>
        </x-ui.card>
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Terminées</p>
            <p class="text-3xl font-display font-bold text-emerald-400">{{ $interventions->where('statut', 'Terminée')->count() }}</p>
        </x-ui.card>
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Coût total</p>
            <p class="text-3xl font-display font-bold text-white">{{ number_format($interventions->sum('cout'), 2, ',', ' ') }} <span class="text-base text-cyan-100/60">DT</span></p>
        </x-ui.card>
    </div>

    {{-- Interventions du technicien (relation 1:N) --}}
    <x-ui.card padding="none" class="overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-white/10">
            <h2 class="font-display font-semibold text-white flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-cyan-300"></i> Interventions réalisées
            </h2>
            <x-maintenance.link-button :href="route('manager.interventions.create', ['technicien_id' => $technicien->id])" icon="plus" class="!py-2 !text-xs">
                Nouvelle intervention
            </x-maintenance.link-button>
        </div>

        @if($interventions->isEmpty())
            <x-ui.empty-state icon="clipboard-list" title="Aucune intervention" description="Ce technicien n'a pas encore d'intervention." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Date</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Description</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Statut</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Coût</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($interventions as $intervention)
                            <tr class="hover:bg-white/[.03] transition-colors">
                                <td class="px-5 py-3 text-cyan-100/80 whitespace-nowrap">{{ $intervention->date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-white">{{ Str::limit($intervention->description, 70) }}</td>
                                <td class="px-5 py-3"><x-maintenance.badge :value="$intervention->statut" /></td>
                                <td class="px-5 py-3 text-right text-cyan-100/80 whitespace-nowrap">{{ number_format($intervention->cout, 2, ',', ' ') }} DT</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('manager.interventions.show', $intervention) }}" class="p-2 inline-flex rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Voir"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
