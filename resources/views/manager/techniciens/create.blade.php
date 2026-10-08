@extends('layouts.manager')

@section('title', 'Nouveau technicien — AquaSecure')

@section('manager-content')
<div class="max-w-3xl mx-auto">
    <x-maintenance.page-header title="Nouveau technicien" subtitle="Ajouter un technicien à l'équipe de maintenance" icon="user-plus" />

    <x-ui.card>
        <form method="POST" action="{{ route('manager.techniciens.store') }}">
            @include('manager.techniciens._form', ['submitLabel' => 'Enregistrer'])
        </form>
    </x-ui.card>
</div>
@endsection
