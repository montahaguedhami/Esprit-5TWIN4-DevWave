@extends('layouts.manager')

@section('title', 'Modifier une intervention — AquaSecure')

@section('manager-content')
<div class="max-w-3xl mx-auto">
    <x-maintenance.page-header title="Modifier l'intervention" :subtitle="'Intervention #'.$intervention->id" icon="clipboard-pen" />

    <x-ui.card>
        <form method="POST" action="{{ route('manager.interventions.update', $intervention) }}">
            @method('PUT')
            @include('manager.interventions._form', ['submitLabel' => 'Mettre à jour'])
        </form>
    </x-ui.card>
</div>
@endsection
