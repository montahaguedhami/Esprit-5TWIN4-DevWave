@extends('layouts.manager')

@section('title', 'Créer une zone — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-3xl space-y-5">
    <div>
        <a href="{{ route('manager.zones.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">← Retour aux zones</a>
        <h1 class="mt-2 text-2xl font-semibold text-white">Nouvelle zone</h1>
    </div>
    @include('manager.shared.flash')
    <form method="POST" action="{{ route('manager.zones.store') }}" class="space-y-4 rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 text-cyan-50 shadow-sm">
        @include('manager.zones._form', ['zone' => null])
    </form>
</div>
@endsection
