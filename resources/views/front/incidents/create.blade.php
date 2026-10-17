@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="max-w-3xl mx-auto py-8 sm:py-10">
    <div class="mb-6">
        <p class="text-sm font-semibold text-cyan-700">Portail citoyen</p>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Signaler un incident</h1>
    </div>

    <a href="{{ route('incidents.index') }}" class="inline-flex items-center gap-2 text-cyan-700 hover:text-cyan-900 font-semibold">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>Mes incidents
    </a>
    <form action="{{ route('incidents.store') }}" method="post" enctype="multipart/form-data" class="space-y-5 mt-4 rounded-2xl bg-white border border-slate-200 p-5 sm:p-8 shadow-sm">
        @csrf
        @include('front.incidents.form')
        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-semibold shadow-sm transition-colors"><i data-lucide="send" class="w-4 h-4"></i>Envoyer</button>
    </form>
</div>
@endsection
