@extends('layouts.admin')

@section('title', 'Paramètres du risque — AquaSecure')
@section('page-title', 'Modèle de risque')
@section('admin-content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/70">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Paramètres de l’estimation de panne</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Les valeurs persistent en base. La probabilité est recalculée avec une fonction logistique.</p>
        @if(session('success'))
            <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200">{{ session('success') }}</p>
        @endif
        @if($errors->any())
            <div class="mt-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.infrastructure-risk.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="bias" class="mb-1 block text-sm font-semibold text-slate-800 dark:text-slate-200">Biais logistique</label>
                <input id="bias" name="bias" type="number" step="0.01" min="-10" max="10" value="{{ old('bias', $riskConfig['bias']) }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            </div>
            <fieldset>
                <legend class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200">Poids des caractéristiques (0 à 10)</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach([
                        'age_ratio' => 'Âge / durée de vie',
                        'maintenance_delay' => 'Retard de maintenance',
                        'etat' => 'État actuel',
                        'interventions' => 'Interventions (12 mois)',
                        'incidents' => 'Incidents (12 mois)',
                        'criticite' => 'Criticité',
                        'flapping' => 'Changements d’état (90 jours)',
                    ] as $key => $label)
                        <label class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}
                            <input name="weights[{{ $key }}]" type="number" step="0.01" min="0" max="10" value="{{ old('weights.'.$key, $riskConfig['weights'][$key]) }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <fieldset>
                <legend class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200">Seuils de niveaux (%)</legend>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach(['moyen' => 'Moyen', 'eleve' => 'Élevé', 'critique' => 'Critique'] as $key => $label)
                        <label class="text-sm text-slate-700 dark:text-slate-300">{{ $label }}
                            <input name="thresholds[{{ $key }}]" type="number" min="1" max="100" value="{{ old('thresholds.'.$key, $riskConfig['thresholds'][$key]) }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-600">Enregistrer les paramètres</button>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-amber-300 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
        <div>
            <h2 class="font-bold text-amber-950 dark:text-amber-100">Recalculer toutes les infrastructures</h2>
            <p class="mt-1 text-sm text-amber-900/75 dark:text-amber-100/70">Crée ou actualise le snapshot courant après modification du modèle.</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.infrastructure-risk.recompute') }}" onsubmit="return confirm('Recalculer le risque pour toutes les infrastructures ?')">
            @csrf
            <button type="submit" class="rounded-lg bg-amber-700 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Recalculer tout</button>
        </form>
    </div>
</div>
@endsection
