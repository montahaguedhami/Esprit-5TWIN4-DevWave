@extends('layouts.frontoffice')

@php
    $title = 'Recommandations — AquaSecure';
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Recommandés pour vous</h1>
        <p class="text-cyan-100/55 text-sm mt-1">
            @if($repli)
                Sélection de projets à découvrir.
            @else
                Sélection calculée d'après vos favoris, vos consultations et vos soutiens.
            @endif
        </p>
    </div>

    {{-- ── Bandeau d'honnêteté ────────────────────────────────────────────── --}}
    <div class="glass rounded-2xl p-4 border border-cyan-400/15">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-cyan-400 flex-shrink-0 mt-0.5"></i>
            <p class="text-cyan-100/70 text-xs leading-relaxed">
                @if($repli)
                    Vous n'avez pas encore de favoris, de consultations ni de soutien enregistré.
                    Le moteur n'a donc rien appris de vos préférences : voici les projets
                    actuellement admissibles, présentés par ordre d'ouverture de financement.
                    <strong class="text-white">Ce n'est pas une sélection personnalisée.</strong>
                @else
                    Ce classement est produit par un <strong class="text-white">moteur de règles déterministe</strong>
                    (et non par un modèle d'intelligence artificielle). À données identiques,
                    il renvoie toujours les mêmes scores et le même ordre.
                @endif
            </p>
        </div>
    </div>

    @if($recommandations->isNotEmpty())

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($recommandations as $reco)
        @php
            $r = $reco->projet;
            $ss = [
                'planifie'  => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié'],
                'en_cours'  => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours'],
            ][$r->statut] ?? ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>$r->statut];
        @endphp
        <article class="glass rounded-2xl p-5 hover-lift cursor-pointer"
                 onclick="window.location='{{ route('citizen.projets.show', $r) }}'">

            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }}">
                    {{ $ss['label'] }}
                </span>
                @if($reco->financable)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300">Finançable</span>
                @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/5 text-cyan-100/45">Non finançable</span>
                @endif
            </div>

            <h2 class="text-white font-display font-bold text-base leading-snug mb-2">{{ $r->nom }}</h2>

            @if($r->zones->isNotEmpty())
            <p class="text-cyan-100/50 text-xs mb-3 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3 h-3"></i>
                {{ $r->zones->pluck('nom')->implode(', ') }}
            </p>
            @endif

            @if($r->description)
            <p class="text-cyan-100/70 text-sm leading-relaxed mb-4 line-clamp-3">{{ $r->description }}</p>
            @endif

            <div class="mb-3">
                <div class="flex justify-between text-[11px] text-cyan-100/50 mb-1">
                    <span>Progression</span>
                    <span class="font-bold text-white">{{ $r->progression }}%</span>
                </div>
                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                    <div class="h-full rounded-full {{ $r->progression >= 100 ? 'bg-teal-400' : 'bg-cyan-400' }}"
                         style="width: {{ min($r->progression, 100) }}%"></div>
                </div>
            </div>

            @if($reco->raisons)
            <ul class="space-y-1.5 mb-3">
                @foreach($reco->raisons as $raison)
                <li class="text-[11px] text-cyan-100/65 flex items-start gap-1.5">
                    <i data-lucide="check" class="w-3 h-3 mt-0.5 text-cyan-400 flex-shrink-0"></i>
                    <span>{{ $raison }}</span>
                </li>
                @endforeach
            </ul>
            @endif

            <div class="flex items-center justify-between pt-2 border-t border-white/5">
                @if($reco->financable && $r->budget_disponible > 0)
                <p class="text-[11px] text-cyan-100/45">
                    Reste : <span class="font-bold text-white">{{ number_format($r->budget_disponible, 0, ',', ' ') }} DT</span>
                </p>
                @else
                <p class="text-[11px] text-cyan-100/40">Découverte</p>
                @endif
                <span class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                    Voir détails
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </span>
            </div>
        </article>
        @endforeach
    </div>

    @else
    <!-- État vide : aucun projet admissible -->
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="sparkles" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h2 class="text-xl font-display font-bold text-white mb-2">Aucune recommandation disponible</h2>
        <p class="text-cyan-100/60 mb-6">
            Aucun projet n'est actuellement admissible à la découverte.
        </p>
        <a href="{{ route('citizen.projets.index') }}"
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            Voir tous les projets
        </a>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>
@endpush
