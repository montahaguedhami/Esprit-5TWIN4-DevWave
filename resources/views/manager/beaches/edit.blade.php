@extends('layouts.manager')

@section('manager-content')
<div class="mx-auto max-w-5xl space-y-6 p-6">
    <div><a href="{{ route('manager.beaches.index') }}" class="text-sm font-semibold text-cyan-700">← Retour à la liste</a><h1 class="mt-2 text-2xl font-bold text-slate-900">Mettre à jour {{ $beach->name }}</h1><p class="mt-2 text-sm text-slate-600">Cette fiche est partagée entre les espaces Manager et Citoyen. Les changements enregistrés sont visibles dans les deux listes, selon les règles de publication.</p></div>
    @if(session('success'))<div class="rounded-xl bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-800">Vérifiez les champs signalés avant d'enregistrer.</div>@endif
    <form method="POST" action="{{ route('manager.beaches.update', $beach) }}" class="space-y-4 rounded-2xl border bg-white p-6">
        @csrf @method('PUT')
        <h2 class="text-lg font-bold">Fiche de la plage</h2>
        <label class="block text-sm font-medium">Nom<input name="name" required maxlength="180" value="{{ old('name', $beach->name) }}" class="mt-1 w-full rounded-lg border-slate-300">@error('name')<span class="text-red-600">{{ $message }}</span>@enderror</label>
        <label class="block text-sm font-medium">Gouvernorat<select name="governorate_id" required class="mt-1 w-full rounded-lg border-slate-300">@foreach($governorates as $gov)<option value="{{ $gov->id }}" @selected(old('governorate_id', $beach->governorate_id) == $gov->id)>{{ $gov->name }}{{ $gov->is_verified ? '' : ' (non vérifié)' }}</option>@endforeach</select></label>
        <label class="block text-sm font-medium">Description<textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description', $beach->description) }}</textarea></label>
        <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-medium">Latitude<input name="latitude" type="number" step="0.0000001" min="-90" max="90" value="{{ old('latitude', $beach->latitude) }}" class="mt-1 w-full rounded-lg border-slate-300"></label><label class="text-sm font-medium">Longitude<input name="longitude" type="number" step="0.0000001" min="-180" max="180" value="{{ old('longitude', $beach->longitude) }}" class="mt-1 w-full rounded-lg border-slate-300"></label></div>
        <label class="flex gap-2"><input type="checkbox" name="coordinates_verified" value="1" @checked(old('coordinates_verified', $beach->coordinates_verified))> Coordonnées vérifiées</label>
        <label class="flex gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $beach->is_published))> Publier dans le catalogue citoyen</label>
        <button class="rounded-lg bg-cyan-700 px-5 py-2 font-semibold text-white">Enregistrer les changements</button>
    </form>

    <section class="space-y-4 rounded-2xl border bg-white p-6">
        <div><h2 class="text-lg font-bold">Nouvelle alerte ou décision de baignade</h2><p class="text-sm text-slate-600">Ce formulaire ajoute une décision sourcée à l'historique. Il ne reprend pas les signalements historiques de juin 2026 comme décisions actuelles.</p></div>
        <form method="POST" action="{{ route('manager.beaches.statuses.store', $beach) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <label class="text-sm font-medium">Décision<select name="status" required class="mt-1 w-full rounded-lg border-slate-300"><option value="">Choisir une décision</option><option value="prohibited" @selected(old('status') === 'prohibited')>Baignade interdite</option><option value="restricted" @selected(old('status') === 'restricted')>Baignade restreinte</option><option value="safe" @selected(old('status') === 'safe')>Baignade autorisée</option></select>@error('status')<span class="text-red-600">{{ $message }}</span>@enderror</label>
            <label class="text-sm font-medium">Source officielle / organisme<input name="source_name" required maxlength="180" value="{{ old('source_name') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-medium">URL de la source<input name="source_url" type="url" value="{{ old('source_url') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-medium">Référence<input name="source_reference" maxlength="120" value="{{ old('source_reference') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-medium">Date de publication<input name="issued_at" type="datetime-local" value="{{ old('issued_at') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-medium">Valide à partir de<input name="valid_from" type="datetime-local" required value="{{ old('valid_from', now()->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-medium">Valide jusqu'au<input name="valid_until" type="datetime-local" required value="{{ old('valid_until') }}" class="mt-1 w-full rounded-lg border-slate-300">@error('valid_until')<span class="text-red-600">{{ $message }}</span>@enderror</label>
            <label class="flex items-center gap-2 self-end"><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified'))> Source et décision vérifiées</label>
            <div class="sm:col-span-2"><button class="rounded-lg bg-amber-600 px-5 py-2 font-semibold text-white">Enregistrer la décision</button></div>
        </form>
    </section>
    <section class="rounded-2xl border bg-white p-6"><h2 class="mb-3 text-lg font-bold">Historique des décisions pour cette plage</h2>@forelse($beach->bathingStatuses as $status)<div class="border-t py-3 text-sm"><strong>{{ ucfirst($status->status) }}</strong> · {{ $status->source_name }} · {{ $status->valid_from->format('d/m/Y H:i') }} – {{ $status->valid_until->format('d/m/Y H:i') }} · {{ $status->is_verified ? 'Vérifiée' : 'Non vérifiée' }}</div>@empty<p class="text-sm text-slate-500">Aucune décision de baignade n’a été enregistrée pour cette plage. Son statut actuel est inconnu.</p>@endforelse</section>
    @if($historicalPoints->isNotEmpty())
    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6"><h2 class="text-lg font-bold text-amber-950">Points signalés dans {{ $beach->governorate->name }} — 20/06/2026</h2><p class="mt-1 text-sm text-amber-900">Ce relevé historique ne décrit pas l’état actuel de la plage « {{ $beach->name }} » et les points ne lui sont pas automatiquement associés.</p><ul class="mt-3 list-inside list-disc space-y-1 text-sm">@foreach($historicalPoints as $point)<li>{{ $point->point_name }} — {{ $point->reported_status }} (historique)</li>@endforeach</ul><p class="mt-3 text-xs text-amber-900">Source : {{ $historicalPoints->first()->source_name }}</p></section>
    @endif
</div>
@endsection
