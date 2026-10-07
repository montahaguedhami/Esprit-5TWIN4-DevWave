@extends('layouts.manager')

@section('title', 'Nouvelle intervention — AquaSecure')

@section('manager-content')
<div class="max-w-3xl mx-auto">
    <x-maintenance.page-header title="Nouvelle intervention" subtitle="Planifier ou enregistrer une intervention de maintenance" icon="clipboard-plus" />

    <x-ui.card>
        @if($techniciens->isEmpty())
            <x-ui.empty-state icon="hard-hat" title="Aucun technicien" description="Ajoutez d'abord un technicien avant de créer une intervention.">
                <x-maintenance.link-button :href="route('manager.techniciens.create')" icon="plus">Nouveau technicien</x-maintenance.link-button>
            </x-ui.empty-state>
        @else
            <form method="POST" action="{{ route('manager.interventions.store') }}">
                @include('manager.interventions._form', ['submitLabel' => 'Enregistrer'])
            </form>
        @endif
    </x-ui.card>
</div>
@endsection
