@extends('layouts.manager')

@section('title', 'Modifier une zone — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-3xl space-y-5">
    <div>
        <a href="{{ route('manager.zones.show', $zone) }}" class="text-sm text-cyan-700 hover:text-cyan-900">← Retour à la zone</a>
        <h1 class="mt-2 text-2xl font-semibold text-white">Modifier la zone</h1>
    </div>
    @include('manager.shared.flash')
    <form method="POST" action="{{ route('manager.zones.update', $zone) }}" class="space-y-4 rounded-2xl border border-[#d5e3e7] bg-[#f5f9fa] p-5 text-slate-800 shadow-sm">
        @method('PUT')
        @include('manager.zones._form', ['zone' => $zone])
    </form>
</div>
@endsection
