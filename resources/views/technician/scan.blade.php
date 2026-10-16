@extends('layouts.manager')

@section('title', 'Scanner un QR Code Ouvrage — Technicien')

@section('manager-content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 border border-cyan-400/30 flex items-center justify-center mx-auto text-cyan-400">
            <i data-lucide="qr-code" class="w-6 h-6"></i>
        </div>
        <h1 class="text-2xl font-bold font-display text-white">Scanner d'Ouvrage Terrain</h1>
        <p class="text-xs text-cyan-100/60 max-w-sm mx-auto">
            Scannez le code QR apposé sur la station ou entrez son identifiant pour afficher instantanément sa fiche technique.
        </p>
    </div>

    @if(session('error'))
    <div class="p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Scanner visuel interactif mobile-first --}}
    <div class="glass rounded-3xl p-6 border border-white/10 shadow-xl relative overflow-hidden text-center space-y-5">
        {{-- Viseur optique animé --}}
        <div class="relative w-56 h-56 mx-auto rounded-2xl border-2 border-cyan-400/50 bg-slate-950/70 flex items-center justify-center overflow-hidden shadow-inner shadow-cyan-900/30">
            {{-- Ligne laser de scan animée --}}
            <div class="absolute inset-x-0 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-lg shadow-cyan-400 animate-pulse top-1/2 -translate-y-1/2"></div>
            
            {{-- Coins de cadrage --}}
            <div class="absolute top-2 left-2 w-4 h-4 border-t-2 border-l-2 border-cyan-400"></div>
            <div class="absolute top-2 right-2 w-4 h-4 border-t-2 border-r-2 border-cyan-400"></div>
            <div class="absolute bottom-2 left-2 w-4 h-4 border-b-2 border-l-2 border-cyan-400"></div>
            <div class="absolute bottom-2 right-2 w-4 h-4 border-b-2 border-r-2 border-cyan-400"></div>

            <i data-lucide="camera" class="w-12 h-12 text-cyan-400/30"></i>
        </div>

        <p class="text-[11px] text-cyan-100/50">Placez le QR Code de l'infrastructure dans le cadre pour une détection automatique.</p>

        {{-- Formulaire de saisie manuelle ou code scanné --}}
        <form method="POST" action="{{ route('technician.scan.process') }}" class="space-y-3 pt-2 border-t border-white/10">
            @csrf
            <div>
                <label for="code" class="block text-xs font-semibold text-cyan-100/70 text-left mb-1.5">
                    Code QR ou Référence de l'Ouvrage
                </label>
                <div class="relative">
                    <input type="text"
                           id="code"
                           name="code"
                           required
                           placeholder="Ex : INF-TUN-001 ou ID station"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition-all">
                        Ouvrir
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Accès direct à mes ouvrages récents assignés --}}
    @if($myInfras->isNotEmpty())
    <div class="glass rounded-2xl p-5 border border-white/10 space-y-3">
        <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i>
            Accès rapide à mes ouvrages assignés
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($myInfras->take(6) as $inf)
            <a href="{{ route('technician.infrastructures.show', $inf) }}"
               class="p-2.5 rounded-xl bg-slate-900/60 hover:bg-cyan-500/10 border border-white/5 hover:border-cyan-400/30 flex items-center justify-between text-xs transition-all group">
                <span class="font-medium text-white group-hover:text-cyan-300 truncate">{{ $inf->nom }}</span>
                <span class="font-mono text-[10px] text-cyan-100/50 shrink-0 ml-2">{{ $inf->reference ?? '#'.$inf->id }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
