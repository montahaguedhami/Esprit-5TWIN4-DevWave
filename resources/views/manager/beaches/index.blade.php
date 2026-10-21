@extends('layouts.manager')

@section('manager-content')
<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Plages</h1>
        <p class="mt-1 text-sm text-slate-600">Gérez ici les points signalés. Les enregistrements partagés sont utilisés par la recherche citoyenne.</p>
    </div>
    @if(session('success'))<div class="rounded-xl bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>@endif
    <section class="space-y-4 rounded-2xl border bg-white p-5">
        <div><h2 class="text-lg font-bold">Ajouter un point signalé</h2></div>
        @php
            $gouvernoratChoisi = (string) old('governorate_id');
            $statutChoisi = old('reported_status', 'restricted');
            $statutsSignales = ['restricted' => 'Restriction signalée', 'unknown' => 'À confirmer'];
        @endphp
        <form id="advisory-point-form" method="POST" action="{{ route('manager.beaches.advisories.store') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <label class="text-sm font-medium">Nom du point<input name="point_name" required minlength="2" maxlength="255" value="{{ old('point_name') }}" class="mt-1 w-full rounded-lg border-slate-300"><span id="error-point_name" class="mt-1 block text-xs font-medium text-red-600">@error('point_name'){{ $message }}@enderror</span></label>
            <label class="text-sm font-medium">Gouvernorat<select name="governorate_id" required class="mt-1 w-full rounded-lg border-slate-300"><option value="">Choisir</option>@foreach($allGovernorates as $gov)<option value="{{ $gov->id }}" @selected($gouvernoratChoisi === (string) $gov->id)>{{ $gov->name }}</option>@endforeach</select><span id="error-governorate_id" class="mt-1 block text-xs font-medium text-red-600">@error('governorate_id'){{ $message }}@enderror</span></label>
            <label class="text-sm font-medium">État signalé<select name="reported_status" required class="mt-1 w-full rounded-lg border-slate-300">@foreach($statutsSignales as $valeur => $libelle)<option value="{{ $valeur }}" @selected($statutChoisi === $valeur)>{{ $libelle }}</option>@endforeach</select><span id="error-reported_status" class="mt-1 block text-xs font-medium text-red-600">@error('reported_status'){{ $message }}@enderror</span></label>
            <label class="text-sm font-medium">Date du signalement<input type="date" name="reported_at" required max="{{ now()->toDateString() }}" value="{{ old('reported_at', now()->toDateString()) }}" class="mt-1 w-full rounded-lg border-slate-300"><span id="error-reported_at" class="mt-1 block text-xs font-medium text-red-600">@error('reported_at'){{ $message }}@enderror</span></label>
            <label class="text-sm font-medium">Source<input name="source_name" required minlength="2" maxlength="180" value="{{ old('source_name') }}" class="mt-1 w-full rounded-lg border-slate-300"><span id="error-source_name" class="mt-1 block text-xs font-medium text-red-600">@error('source_name'){{ $message }}@enderror</span></label>
            <label class="text-sm font-medium">URL de la source<input type="url" name="source_url" required maxlength="2000" value="{{ old('source_url') }}" class="mt-1 w-full rounded-lg border-slate-300"><span id="error-source_url" class="mt-1 block text-xs font-medium text-red-600">@error('source_url'){{ $message }}@enderror</span></label>
            <div class="sm:col-span-2 lg:col-span-3"><button class="rounded-lg bg-cyan-700 px-4 py-2 font-semibold text-white">Ajouter</button></div>
        </form>
    </section>
    <section class="space-y-4 rounded-2xl border bg-white p-5">
        <div><h2 class="text-lg font-bold">Points signalés partagés avec le citoyen</h2><p class="text-sm text-slate-600">Seuls les points « restricted » apparaissent sur la page citoyenne. Les points « unknown » restent consultables ici.</p></div>
        <form method="GET" action="{{ route('manager.beaches.index') }}" class="grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-[1fr_1fr_auto]">
            <label class="text-sm font-medium">Rechercher par nom de point<input type="search" name="q" value="{{ request('q') }}" maxlength="255" placeholder="Nom de la plage" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="text-sm font-medium">Filtrer par gouvernorat<select name="governorate_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">Tous les gouvernorats</option>@foreach($allGovernorates as $gov)<option value="{{ $gov->id }}" @selected((string) request('governorate_id') === (string) $gov->id)>{{ $gov->name }}</option>@endforeach</select></label>
            <div class="flex items-end"><button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 font-semibold text-white">Rechercher</button></div>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="p-3">Point</th><th class="p-3">Gouvernorat</th><th class="p-3">État</th><th class="p-3">Date</th><th class="p-3">Source</th><th class="p-3">Actions</th></tr></thead><tbody class="divide-y">
                @forelse($advisoryPoints as $point)
                    <tr><td class="p-3 font-semibold">{{ $point->point_name }}</td><td class="p-3">{{ $point->governorate_name }}</td><td class="p-3">@if($point->reported_status === 'restricted')Baignade restreinte (restricted)@elseif($point->reported_status === 'unknown')État inconnu (unknown)@else{{ $point->reported_status }}@endif</td><td class="p-3">{{ $point->reported_at->format('d/m/Y') }}</td><td class="p-3"><a class="text-cyan-700 underline" href="{{ $point->source_url }}" target="_blank" rel="noopener noreferrer">{{ $point->source_name }}</a></td><td class="p-3"><a class="font-semibold text-cyan-700 underline" href="{{ route('manager.beaches.advisories.edit', ['point' => $point] + request()->only('q', 'governorate_id')) }}">Modifier</a></td></tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-slate-500">Aucun point signalé enregistré.</td></tr>
                @endforelse
            </tbody></table>
        </div>
        {{ $advisoryPoints->links() }}
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('advisory-point-form');
    if (!form) return;

    const pointName = form.elements.namedItem('point_name');
    const governorate = form.elements.namedItem('governorate_id');
    const reportedAt = form.elements.namedItem('reported_at');
    const status = form.elements.namedItem('reported_status');
    const sourceName = form.elements.namedItem('source_name');
    const sourceUrl = form.elements.namedItem('source_url');
    const fields = [pointName, governorate, status, reportedAt, sourceName, sourceUrl];

    function errorFor(field) {
        const value = field.value.trim();
        if (!value) return 'Ce champ est obligatoire.';
        if (field === pointName && value.length < 2) return 'Le nom doit contenir au moins 2 caractères.';
        if (field === pointName && value.length > 255) return 'Le nom ne peut pas dépasser 255 caractères.';
        if (field === sourceName && value.length < 2) return 'La source doit contenir au moins 2 caractères.';
        if (field === sourceName && value.length > 180) return 'La source ne peut pas dépasser 180 caractères.';
        if (field === sourceUrl) {
            if (value.length > 2000) return 'L’URL ne peut pas dépasser 2 000 caractères.';
            try {
                const parsedUrl = new URL(value);
                if (!['http:', 'https:'].includes(parsedUrl.protocol)) return 'L’URL doit commencer par http:// ou https://.';
                if (!parsedUrl.hostname) return 'Saisissez une URL complète.';
            } catch (_) {
                return 'Saisissez une URL valide.';
            }
        }
        if (field === reportedAt) {
            const parsedDate = new Date(`${value}T00:00:00Z`);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value) || Number.isNaN(parsedDate.getTime()) || parsedDate.toISOString().slice(0, 10) !== value) return 'Saisissez une date valide.';
            if (value > field.max) return 'La date ne peut pas être dans le futur.';
        }
        if (field === status && !['restricted', 'unknown'].includes(value)) return 'Sélectionnez un état valide.';

        return '';
    }

    function validateField(field) {
        const error = errorFor(field);
        const feedback = document.getElementById(`error-${field.name}`);
        feedback.textContent = error;
        field.setAttribute('aria-invalid', error ? 'true' : 'false');
        return !error;
    }

    form.noValidate = true;
    form.addEventListener('submit', (event) => {
        pointName.value = pointName.value.trim();
        sourceName.value = sourceName.value.trim();
        sourceUrl.value = sourceUrl.value.trim();
        const valid = fields.map(validateField).every(Boolean);
        if (!valid) {
            event.preventDefault();
            fields.find((field) => field.getAttribute('aria-invalid') === 'true')?.focus();
        }
    });

    fields.forEach((field) => {
        field.addEventListener('input', () => validateField(field));
        field.addEventListener('change', () => validateField(field));
    });
})();
</script>
@endpush
