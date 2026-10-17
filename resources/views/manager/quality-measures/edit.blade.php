@extends('layouts.manager')
@section('title', 'Modifier la mesure ' . $mesure->reference)
@section('manager-content')
<div class="text-slate-100">
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Modifier la mesure {{ $mesure->reference }}</h1>
                <p class="text-sm text-slate-400 mt-1">Point : {{ $mesure->pointMesure?->nom ?? 'Point inconnu' }} · Date : {{ $mesure->date_mesure->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('manager.quality.mesures.show', $mesure) }}" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-slate-300 hover:text-white">
                Retour aux détails
            </a>
        </div>

        <form method="POST" action="{{ route('manager.quality.mesures.update', $mesure) }}" id="measure-edit-form" class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            @csrf @method('PUT')
            @include('manager.quality-measures.form')
            
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <a href="{{ route('manager.quality.mesures.show', $mesure) }}" class="rounded-xl bg-slate-800 hover:bg-slate-700 px-4 py-2 text-sm text-slate-200">
                    Annuler
                </a>
                <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-5 py-2 text-sm font-bold text-slate-950 transition-all disabled:opacity-50">
                    <span id="btn-text">Mettre à jour la mesure</span>
                    <span id="btn-spinner" class="hidden"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Réévaluation...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('measure-edit-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    document.getElementById('btn-text').classList.add('hidden');
    document.getElementById('btn-spinner').classList.remove('hidden');
    btn.disabled = true;
});
</script>
@endsection
