@extends(session('user.role') === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Créer une zone — AquaSecure')

@section(session('user.role') === 'admin' ? 'admin-content' : 'manager-content')
@php
    $inventoryRoutePrefix = session('user.role') === 'admin' ? 'admin' : 'manager';
@endphp
<div class="mx-auto max-w-3xl space-y-5">
    <div>
        <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">← Retour aux zones</a>
        <h1 class="mt-2 text-2xl font-semibold text-white">Nouvelle zone</h1>
    </div>
    @include('manager.shared.flash')
    <form method="POST" action="{{ route($inventoryRoutePrefix . '.zones.store') }}" class="space-y-4 rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 text-cyan-50 shadow-sm">
        @include('manager.zones._form', ['zone' => null])
    </form>
</div>
@endsection
