@extends('layouts.manager')
@section('title', 'Nouvelle mesure de qualité')
@section('content')
<div class="min-h-screen bg-[#061525] p-4 text-slate-100 sm:p-8">
    <div class="mx-auto max-w-3xl space-y-5">
        <h1 class="text-2xl font-bold text-white">Enregistrer une mesure de qualité</h1>
        <form method="POST" action="{{ route('manager.quality.mesures.store') }}" class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @include('manager.quality-measures.form')
            <div class="flex justify-end gap-3"><a href="{{ route('manager.quality') }}" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Annuler</a><button class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-slate-950">Enregistrer</button></div>
        </form>
    </div>
</div>
@endsection
