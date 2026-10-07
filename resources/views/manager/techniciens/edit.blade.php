@extends('layouts.manager')

@section('title', 'Modifier un technicien — AquaSecure')

@section('manager-content')
<div class="max-w-3xl mx-auto">
    <x-maintenance.page-header title="Modifier le technicien" :subtitle="$technicien->nom" icon="user-cog" />

    <x-ui.card>
        <form method="POST" action="{{ route('manager.techniciens.update', $technicien) }}">
            @method('PUT')
            @include('manager.techniciens._form', ['submitLabel' => 'Mettre à jour'])
        </form>
    </x-ui.card>
</div>
@endsection
