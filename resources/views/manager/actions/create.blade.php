@extends('layouts.manager')

@section('manager-content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('manager.actions.index', $incident) }}" class="text-cyan-300">Retour aux actions</a>
    <h1 class="text-2xl font-bold mt-6">Ajouter une action corrective</h1>
    <p class="mt-2 text-slate-300 wrap-break-word">Incident : {{ $incident->titre }}</p>
    <form action="{{ route('incidents.actions.store', $incident) }}" method="post" class="space-y-4 mt-6">
        @csrf
        @include('manager.actions.form', ['action' => null])
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter l'action</button>
    </form>
</div>
@endsection