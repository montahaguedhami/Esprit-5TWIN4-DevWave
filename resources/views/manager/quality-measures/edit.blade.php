@extends('layouts.manager')
@section('title', 'Modifier une mesure de qualité')
@section('manager-content')
<div class="text-slate-800">
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Modifier la mesure {{ $mesure->reference }}</h1>
                <p class="mt-1 text-sm text-slate-600">Point : {{ $mesure->pointMesure?->nom ?? 'Point inconnu' }} · Date : {{ $mesure->date_mesure->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('manager.quality.mesures.show', $mesure) }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-cyan-400 hover:bg-cyan-50 hover:text-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                Retour aux détails
            </a>
        </div>

        <form method="POST" action="{{ route('manager.quality.mesures.update', $mesure) }}" id="measure-edit-form" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf @method('PUT')
            @include('manager.quality-measures.form')
            
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-3">
                <a href="{{ route('manager.quality.mesures.show', $mesure) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                    Annuler
                </a>
                <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-text">Mettre à jour la mesure</span>
                    <span id="btn-spinner" class="hidden"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Réévaluation...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
