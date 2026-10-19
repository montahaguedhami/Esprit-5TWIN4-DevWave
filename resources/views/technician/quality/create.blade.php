@extends('layouts.technician')

@section('title', 'Nouvelle mesure qualité — Technicien')

@section('technician-content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('technician.zones.show', $zone) }}" class="text-sm text-cyan-300 hover:text-white">← Retour à {{ $zone->nom }}</a>
        <h1 class="mt-3 text-2xl font-bold text-white">Saisir une mesure de qualité</h1>
        <p class="mt-1 text-sm text-cyan-100/60">La mesure restera interne jusqu’à sa validation par un gestionnaire.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('technician.quality.store', $zone) }}" class="grid gap-5 rounded-2xl border border-white/10 bg-slate-900/70 p-6 sm:grid-cols-2">
        @csrf
        <label class="text-sm text-slate-200 sm:col-span-2">Point de mesure
            <select name="point_mesure_id" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 p-3 text-white">
                @foreach($points as $point)<option value="{{ $point->id }}" @selected(old('point_mesure_id') == $point->id)>{{ $point->code }} — {{ $point->nom }}{{ $point->infrastructure ? ' · '.$point->infrastructure->nom : '' }} ({{ $point->adresse ?? $point->zone }})</option>@endforeach
            </select>
        </label>
        <label class="text-sm text-slate-200 sm:col-span-2">Date et heure du prélèvement
            <input type="datetime-local" name="date_mesure" value="{{ old('date_mesure', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 p-3 text-white">
        </label>
        @foreach([['ph','pH','0','14','0.01'],['turbidite','Turbidité (NTU)','0','1000','0.01'],['chlore_residuel','Chlore résiduel (mg/L)','0','100','0.001'],['plomb','Plomb (ppb)','0','10000','0.001'],['nitrates','Nitrates (mg/L)','0','1000','0.01']] as [$field,$label,$min,$max,$step])
            <label class="text-sm text-slate-200">{{ $label }}
                <input type="number" name="{{ $field }}" value="{{ old($field) }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 p-3 text-white">
            </label>
        @endforeach
        <div class="flex justify-end gap-3 sm:col-span-2">
            <a href="{{ route('technician.zones.show', $zone) }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white">Annuler</a>
            <button class="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-bold text-slate-950">Enregistrer pour validation</button>
        </div>
    </form>
</div>
@endsection
