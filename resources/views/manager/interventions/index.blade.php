@extends('layouts.manager')

@section('title', 'Interventions — AquaSecure')

@section('manager-content')
<div class="max-w-6xl mx-auto">
    <x-maintenance.page-header title="Interventions" subtitle="Suivi des interventions de maintenance sur le réseau" icon="clipboard-list">
        <x-maintenance.link-button :href="route('manager.techniciens.index')" variant="secondary" icon="hard-hat">Techniciens</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.interventions.create', request()->only('technicien_id'))" icon="plus">Nouvelle intervention</x-maintenance.link-button>
    </x-maintenance.page-header>

    @include('manager.maintenance._flash')

    {{-- Filtres --}}
    <x-ui.card padding="sm" class="mb-6">
        <form method="GET" action="{{ route('manager.interventions.index') }}" class="grid sm:grid-cols-3 gap-3 items-end">
            <x-forms.select name="technicien_id" label="Technicien" placeholder="Tous les techniciens">
                @foreach($techniciens as $technicien)
                    <option value="{{ $technicien->id }}" @selected(request('technicien_id') == $technicien->id)>{{ $technicien->nom }}</option>
                @endforeach
            </x-forms.select>

            <x-forms.select name="statut" label="Statut" placeholder="Tous les statuts">
                @foreach(\App\Models\Intervention::STATUTS as $statut)
                    <option value="{{ $statut }}" @selected(request('statut') === $statut)>{{ $statut }}</option>
                @endforeach
            </x-forms.select>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="filter" class="flex-1">Filtrer</x-ui.button>
                @if(request()->hasAny(['technicien_id', 'statut']))
                    <x-maintenance.link-button :href="route('manager.interventions.index')" variant="secondary" title="Réinitialiser">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </x-maintenance.link-button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        @if($interventions->isEmpty())
            <x-ui.empty-state icon="clipboard-list" title="Aucune intervention" description="Aucune intervention ne correspond à vos filtres." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Date</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Technicien</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Description</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Statut</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Coût</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($interventions as $intervention)
                            <tr class="hover:bg-white/[.03] transition-colors">
                                <td class="px-5 py-3 text-cyan-100/80 whitespace-nowrap">{{ $intervention->date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('manager.techniciens.show', $intervention->technicien) }}" class="font-semibold text-white hover:text-cyan-300">{{ $intervention->technicien->nom }}</a>
                                </td>
                                <td class="px-5 py-3 text-cyan-100/80">{{ Str::limit($intervention->description, 60) }}</td>
                                <td class="px-5 py-3"><x-maintenance.badge :value="$intervention->statut" /></td>
                                <td class="px-5 py-3 text-right text-cyan-100/80 whitespace-nowrap">{{ number_format($intervention->cout, 2, ',', ' ') }} DT</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('manager.interventions.show', $intervention) }}" class="p-2 rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Voir"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                        <a href="{{ route('manager.interventions.edit', $intervention) }}" class="p-2 rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Modifier"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                        <x-maintenance.delete-button :action="route('manager.interventions.destroy', $intervention)" size="sm" label=""
                                            confirm="Supprimer cette intervention ?" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-maintenance.pagination :paginator="$interventions" />
        @endif
    </x-ui.card>
</div>
@endsection
