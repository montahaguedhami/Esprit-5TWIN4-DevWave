@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="max-w-3xl mx-auto py-12">
    <h1 class="text-2xl font-bold mb-4">Modifier mon incident</h1>
    <a href="{{ route('incidents.show', $incident) }}" class="text-cyan-300">Retour &agrave; l'incident</a>
    <form action="{{ route('incidents.update', $incident) }}" method="post" class="space-y-4 mt-6">
        @csrf
        @method('PUT')
        @include('front.incidents.form')
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="save" class="w-4 h-4"></i>Enregistrer</button>
    </form>
</div>
@endsection