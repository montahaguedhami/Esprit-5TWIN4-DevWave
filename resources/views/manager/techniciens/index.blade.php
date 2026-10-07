@extends('layouts.manager')

@section('title', 'Techniciens — AquaSecure')

@section('manager-content')
<div class="max-w-6xl mx-auto">
    <x-maintenance.page-header title="Techniciens" subtitle="Équipe de maintenance du réseau d'eau" icon="hard-hat">
        <x-maintenance.link-button :href="route('manager.interventions.index')" variant="secondary" icon="clipboard-list">Interventions</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.techniciens.create')" icon="plus">Nouveau technicien</x-maintenance.link-button>
    </x-maintenance.page-header>

    @include('manager.maintenance._flash')

    {{-- Filtres --}}
    <x-ui.card padding="sm" class="mb-6">
        <form method="GET" action="{{ route('manager.techniciens.index') }}" class="grid sm:grid-cols-4 gap-3 items-end">
            <x-forms.input name="q" label="Rechercher" icon="search" placeholder="Nom du technicien" :value="request('q')" />

            <x-forms.select name="specialite" label="Spécialité" placeholder="Toutes">
                @foreach(\App\Models\Technicien::SPECIALITES as $specialite)
                    <option value="{{ $specialite }}" @selected(request('specialite') === $specialite)>{{ $specialite }}</option>
                @endforeach
            </x-forms.select>

            <x-forms.select name="disponibilite" label="Disponibilité" placeholder="Toutes">
                @foreach(\App\Models\Technicien::DISPONIBILITES as $disponibilite)
                    <option value="{{ $disponibilite }}" @selected(request('disponibilite') === $disponibilite)>{{ $disponibilite }}</option>
                @endforeach
            </x-forms.select>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="filter" class="flex-1">Filtrer</x-ui.button>
                @if(request()->hasAny(['q', 'specialite', 'disponibilite']))
                    <x-maintenance.link-button :href="route('manager.techniciens.index')" variant="secondary" title="Réinitialiser">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </x-maintenance.link-button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        @if($techniciens->isEmpty())
            <x-ui.empty-state icon="hard-hat" title="Aucun technicien" description="Aucun technicien ne correspond à votre recherche." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Nom</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Spécialité</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Téléphone</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Disponibilité</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-center">Interventions</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($techniciens as $technicien)
                            <tr class="hover:bg-white/[.03] transition-colors">
                                <td class="px-5 py-3">
                                    <a href="{{ route('manager.techniciens.show', $technicien) }}" class="font-semibold text-white hover:text-cyan-300">{{ $technicien->nom }}</a>
                                </td>
                                <td class="px-5 py-3 text-cyan-100/80">{{ $technicien->specialite }}</td>
                                <td class="px-5 py-3 text-cyan-100/80 whitespace-nowrap">{{ $technicien->telephone }}</td>
                                <td class="px-5 py-3"><x-maintenance.badge :value="$technicien->disponibilite" /></td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('manager.interventions.index', ['technicien_id' => $technicien->id]) }}" class="inline-flex min-w-8 justify-center rounded-lg bg-cyan-500/10 border border-cyan-400/20 px-2 py-0.5 font-semibold text-cyan-300 hover:bg-cyan-500/20">
                                        {{ $technicien->interventions_count }}
                                    </a>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('manager.techniciens.show', $technicien) }}" class="p-2 rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Voir"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                        <a href="{{ route('manager.techniciens.edit', $technicien) }}" class="p-2 rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Modifier"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                        <x-maintenance.delete-button :action="route('manager.techniciens.destroy', $technicien)" size="sm" label=""
                                            confirm="Supprimer ce technicien ? Ses interventions seront aussi supprimées." />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-maintenance.pagination :paginator="$techniciens" />
        @endif
    </x-ui.card>
</div>
@endsection
