@extends('layouts.manager')

@section('title', 'Incidents & actions correctives')

@section('manager-content')
<div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-cyan-300">Espace technicien · {{ $technicien->nom }}</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Incidents & actions correctives</h1>
            <p class="mt-1 text-sm text-slate-300">Les actions qui vous sont affectées et leur état actuel.</p>
        </div>
        <span class="rounded-lg border border-white/10 px-3 py-2 text-sm text-slate-300">{{ $actions->total() }} action(s)</span>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-lg border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif

    <div class="divide-y divide-white/10 border-y border-white/10">
        @forelse($actions as $action)
            <article class="grid gap-5 py-5 lg:grid-cols-[minmax(0,1fr)_260px]">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-cyan-300">Incident #{{ $action->incident_id }}</p>
                    <h2 class="mt-1 text-lg font-semibold text-white">{{ $action->incident?->titre ?? 'Incident supprimé' }}</h2>
                    @if($action->incident)
                        <p class="mt-1 text-sm text-slate-300">{{ $action->incident->type }} · Gravité {{ $action->incident->gravite }}</p>
                        <p class="mt-2 text-sm text-slate-300">Localisation : {{ $action->incident->localisation }}</p>
                        <p class="mt-1 text-xs text-slate-400">Signalé par {{ $action->incident->user?->name ?? 'Citoyen' }}</p>
                    @endif
                    <div class="mt-4 border-l-2 border-cyan-400/50 pl-3">
                        <h3 class="font-semibold text-white">{{ $action->titre }}</h3>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-300">{{ $action->description }}</p>
                        @if($action->resultat)
                            <p class="mt-2 text-sm text-slate-300"><span class="font-medium text-slate-200">Résultat :</span> {{ $action->resultat }}</p>
                        @endif
                    </div>
                    <p class="mt-3 text-xs text-slate-400">Échéance : {{ $action->date_prevue?->format('d/m/Y') ?? 'Non définie' }}</p>
                </div>

                <form action="{{ route('technician.incidents.actions.status', $action) }}" method="post" class="self-start rounded-lg border border-white/10 bg-white/3 p-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-white">Statut actuel</span>
                        <x-incident-action-status :status="$action->statut" />
                    </div>
                    <label for="action-status-{{ $action->id }}" class="block text-sm font-semibold text-white">Statut de l’action</label>
                    <select id="action-status-{{ $action->id }}" name="statut" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white">
                        @foreach(['a_faire' => 'À faire', 'en_cours' => 'En cours', 'terminee' => 'Terminée'] as $value => $label)
                            <option value="{{ $value }}" @selected($action->statut === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('statut') <p class="mt-2 text-xs text-red-300">{{ $message }}</p> @enderror
                    <button type="submit" class="mt-3 w-full rounded-lg bg-cyan-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-400">Enregistrer</button>
                    @if($action->statut === 'terminee' && $action->date_realisation)
                        <p class="mt-2 text-center text-xs text-slate-400">Terminée le {{ $action->date_realisation->format('d/m/Y H:i') }}</p>
                    @endif
                </form>
            </article>
        @empty
            <div class="py-16 text-center">
                <i data-lucide="clipboard-check" class="mx-auto h-10 w-10 text-slate-500"></i>
                <h2 class="mt-3 font-semibold text-white">Aucune action affectée</h2>
                <p class="mt-1 text-sm text-slate-400">Les actions correctives qui vous seront attribuées apparaîtront ici.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $actions->links() }}</div>
</div>
@endsection
