@extends(session('user.role') === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Modifier une infrastructure — AquaSecure')

@section(session('user.role') === 'admin' ? 'admin-content' : 'manager-content')
@php
    $inventoryRoutePrefix = session('user.role') === 'admin' ? 'admin' : 'manager';
@endphp
<div class="mx-auto max-w-3xl space-y-5">
    <div>
        <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infrastructure) }}" class="text-sm text-cyan-300 hover:text-cyan-200">← Retour à l’infrastructure</a>
        <h1 class="mt-2 text-2xl font-semibold text-white">Modifier l’infrastructure</h1>
    </div>
    @include('manager.shared.flash')
    <form method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.update', $infrastructure) }}" class="space-y-4 rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 text-cyan-50 shadow-sm">
        @method('PUT')
        @include('manager.infrastructures._form', ['infrastructure' => $infrastructure])
    </form>
</div>
@endsection
