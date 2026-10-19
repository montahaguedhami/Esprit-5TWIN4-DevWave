@extends('layouts.frontoffice')

@php
    $title = 'Mes favoris — AquaSecure';
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Mes favoris</h1>
        <p class="text-cyan-100/55 text-sm mt-1">
            Les projets que vous avez choisis de sauvegarder.
            @if($favoris->total() > 0)
            <span class="text-cyan-100/40">({{ $favoris->total() }} projet{{ $favoris->total() > 1 ? 's' : '' }})</span>
            @endif
        </p>
    </div>

    @if($favoris->count() > 0)

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($favoris as $projet)
        @php
            $ss = [
                'planifie'  => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié'],
                'en_cours'  => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours'],
                'termine'   => ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Terminé'],
                'suspendu'  => ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>'Suspendu'],
                'annule'    => ['bg'=>'bg-red-500/15','text'=>'text-red-300','label'=>'Annulé'],
            ][$projet->statut] ?? ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>$projet->statut];
        @endphp
        <article class="glass rounded-2xl p-5 hover-lift cursor-pointer"
                 onclick="window.location='{{ route('citizen.projets.show', $projet) }}'">

            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }}">
                    {{ $ss['label'] }}
                </span>
                {{-- Pile de retrait. `data-url` pointe sur la route DELETE ; le
                     script envoie la bonne méthode selon l'état courant. --}}
                <button type="button"
                        class="favori-toggle w-8 h-8 rounded-lg inline-flex items-center justify-center border transition-colors bg-amber-400/15 border-amber-400/40 text-amber-300 hover:text-white"
                        data-projet-id="{{ $projet->id }}"
                        data-url="{{ route('citizen.projets.favori.basculer', $projet) }}"
                        data-retour="page"
                        data-etat="1"
                        aria-pressed="true"
                        aria-label="Retirer ce projet de vos favoris"
                        title="Retirer des favoris">
                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                </button>
            </div>

            <h2 class="text-white font-display font-bold text-base leading-snug mb-2">
                {{ $projet->nom }}
            </h2>

            @if($projet->zones->isNotEmpty())
            <p class="text-cyan-100/50 text-xs mb-3 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3 h-3"></i>
                {{ $projet->zones->pluck('nom')->implode(', ') }}
            </p>
            @endif

            @if($projet->description)
            <p class="text-cyan-100/70 text-sm leading-relaxed mb-4 line-clamp-3">
                {{ \Illuminate\Support\Str::limit($projet->description, 130) }}
            </p>
            @endif

            <div class="mb-3">
                <div class="flex justify-between text-[11px] text-cyan-100/50 mb-1">
                    <span>Progression</span>
                    <span class="font-bold text-white">{{ $projet->progression }}%</span>
                </div>
                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                    <div class="h-full rounded-full {{ $projet->progression >= 100 ? 'bg-teal-400' : 'bg-cyan-400' }}"
                         style="width: {{ min($projet->progression, 100) }}%"></div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-white/5">
                <p class="text-[11px] text-cyan-100/45">
                    @if($projet->date_debut)
                    Début : {{ $projet->date_debut->format('m/Y') }}
                    @endif
                </p>
                <span class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                    Voir détails
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </span>
            </div>
        </article>
        @endforeach
    </div>

    @if($favoris->hasPages())
    <div class="glass rounded-2xl p-4">
        {{ $favoris->links() }}
    </div>
    @endif

    @else
    <!-- État vide -->
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="star" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h2 class="text-xl font-display font-bold text-white mb-2">Aucun favori pour l'instant</h2>
        <p class="text-cyan-100/60 mb-6">
            Cliquez sur l'étoile d'un projet pour le sauvegarder ici et retrouver facilement
            les chantiers qui vous intéressent.
        </p>
        <a href="{{ route('citizen.projets.index') }}"
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            <i data-lucide="briefcase" class="w-4 h-4"></i>
            Parcourir les projets
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
