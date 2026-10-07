@extends('layouts.manager')

@section('title', 'Modifier une infrastructure — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-3xl space-y-5">
    <div>
        <a href="{{ route('manager.infrastructures.show', $infrastructure) }}" class="text-sm text-cyan-700 hover:text-cyan-900">← Retour à l’infrastructure</a>
        <h1 class="mt-2 text-2xl font-semibold text-white">Modifier l’infrastructure</h1>
    </div>
    @include('manager.shared.flash')
    <form method="POST" action="{{ route('manager.infrastructures.update', $infrastructure) }}" class="space-y-4 rounded-2xl border border-[#d5e3e7] bg-[#f5f9fa] p-5 text-slate-800 shadow-sm">
        @method('PUT')
        @include('manager.infrastructures._form', ['infrastructure' => $infrastructure])
    </form>
</div>
@endsection
