@extends('layouts.manager')

@section('manager-content')
<div class="mx-auto max-w-3xl space-y-6 p-6">
    <div>
        <a href="{{ route('manager.beaches.index', request()->query()) }}" class="text-sm font-semibold text-cyan-700">&larr; Retour &agrave; la liste</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Modifier le signalement</h1>
        <p class="mt-1 text-sm text-slate-600">Les modifications du point restreint sont reprises dans la liste citoyenne.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">Corrigez les champs signalés avant d'enregistrer.</div>
    @endif

    <form method="POST" action="{{ route('manager.beaches.advisories.update', array_merge(['point' => $point], request()->query())) }}" class="space-y-4 rounded-2xl border bg-white p-6">
        @csrf
        @method('PUT')

        <label class="block text-sm font-medium">Nom du site
            <input name="point_name" required minlength="2" maxlength="255" value="{{ old('point_name', $point->point_name) }}" class="mt-1 w-full rounded-lg border-slate-300" autocomplete="off">
            @error('point_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="block text-sm font-medium">Gouvernorat
            <select name="governorate_id" required class="mt-1 w-full rounded-lg border-slate-300">
                @foreach($governorates as $governorate)
                    <option value="{{ $governorate->id }}" @selected(old('governorate_id', $governorates->firstWhere('name', $point->governorate_name)?->id) == $governorate->id)>{{ $governorate->name }}</option>
                @endforeach
            </select>
            @error('governorate_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="block text-sm font-medium">Statut du signalement
            <select name="reported_status" required class="mt-1 w-full rounded-lg border-slate-300">
                <option value="restricted" @selected(old('reported_status', $point->reported_status) === 'restricted')>Baignade restreinte</option>
                <option value="unknown" @selected(old('reported_status', $point->reported_status) === 'unknown')>&Agrave; confirmer</option>
            </select>
            <span class="mt-1 block text-xs text-slate-500">Seuls les signalements restreints apparaissent dans la recherche citoyenne.</span>
            @error('reported_status')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="block text-sm font-medium">Date du signalement
            <input type="date" name="reported_at" required max="{{ now()->toDateString() }}" value="{{ old('reported_at', $point->reported_at->toDateString()) }}" class="mt-1 w-full rounded-lg border-slate-300">
            @error('reported_at')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="block text-sm font-medium">Source
            <input name="source_name" required minlength="2" maxlength="180" value="{{ old('source_name', $point->source_name) }}" class="mt-1 w-full rounded-lg border-slate-300">
            @error('source_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="block text-sm font-medium">URL de la source
            <input type="url" name="source_url" required maxlength="2000" value="{{ old('source_url', $point->source_url) }}" class="mt-1 w-full rounded-lg border-slate-300">
            @error('source_url')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
        </label>

        <div class="flex flex-wrap gap-3 pt-2">
            <button class="rounded-lg bg-cyan-700 px-5 py-2 font-semibold text-white">Enregistrer</button>
            <a href="{{ route('manager.beaches.index', request()->query()) }}" class="rounded-lg border border-slate-300 px-5 py-2 font-semibold text-slate-700">Annuler</a>
        </div>
    </form>
</div>
@endsection
