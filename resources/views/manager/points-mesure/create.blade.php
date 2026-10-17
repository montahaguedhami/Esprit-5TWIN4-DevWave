@extends('layouts.manager')
@section('title', 'Nouveau point de prélèvement')
@section('manager-content')
<div class="text-slate-100">
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Créer un point de prélèvement</h1>
                <p class="text-sm text-slate-400 mt-1">Enregistrez un nouveau site d'échantillonnage pour la surveillance de la qualité.</p>
            </div>
            <a href="{{ route('manager.points-mesure.index') }}" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-slate-300 hover:text-white">
                Retour à la liste
            </a>
        </div>

        <form method="POST" action="{{ route('manager.points-mesure.store') }}" id="point-form" class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @include('manager.points-mesure.form')
            
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <a href="{{ route('manager.points-mesure.index') }}" class="rounded-xl bg-slate-800 hover:bg-slate-700 px-4 py-2 text-sm text-slate-200">
                    Annuler
                </a>
                <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 rounded-xl bg-teal-500 hover:bg-teal-400 px-5 py-2 text-sm font-bold text-slate-950 transition-all disabled:opacity-50">
                    <span id="btn-text">Créer le point</span>
                    <span id="btn-spinner" class="hidden"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Enregistrement...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('point-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    document.getElementById('btn-text').classList.add('hidden');
    document.getElementById('btn-spinner').classList.remove('hidden');
    btn.disabled = true;
});
</script>
@endsection
