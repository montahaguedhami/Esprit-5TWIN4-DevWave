{{--
    Bloc « Recommandés pour vous » — moteur V1 à règles, sans Machine Learning.

    Rendu côté serveur, à partir de la session authentifiée uniquement.
    Aucun historique privé n'est envoyé à un autre utilisateur.

    `$compact` : version resserrée pour être insérée en haut de la page projets.
--}}
@php
    // Le bloc n'est inclus que s'il reste des projets admissibles à proposer.
    $vide = ($recommandations ?? collect())->isEmpty();
@endphp

@if(!$vide)
<section class="glass rounded-2xl p-5" aria-labelledby="titre-recommandations">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 id="titre-recommandations" class="text-lg font-display font-bold text-white flex items-center gap-2">
                <i data-lucide="sparkles" class="w-5 h-5 text-cyan-400"></i>
                Recommandés pour vous
            </h2>
            <p class="text-cyan-100/50 text-xs mt-1">
                @if($repli ?? false)
                    {{-- Formulation honnête : le moteur n'a encore rien appris de ce
                         citoyen. On ne présente PAS cela comme une personnalisation. --}}
                    Aucun historique pour l'instant — voici une sélection de projets à découvrir.
                @else
                    Sélection calculée d'après vos favoris, vos consultations et vos soutiens.
                @endif
            </p>
        </div>

        <a href="{{ route('citizen.recommandations') }}"
           class="text-xs text-cyan-300 hover:text-white font-semibold inline-flex items-center gap-1">
            Tout voir
            <i data-lucide="arrow-right" class="w-3 h-3"></i>
        </a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($recommandations as $reco)
        @php
            $r  = $reco->projet;
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

                {{-- Badge d'éligibilité : on ne présente JAMAIS comme finançable
                     un projet qui ne peut plus recevoir d'argent. --}}
                @if($reco->financable)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300">
                    Finançable
                </span>
                @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/5 text-cyan-100/45">
                    Non finançable
                </span>
                @endif
            </div>

            <h3 class="text-white font-display font-bold text-base leading-snug mb-2">
                {{ $r->nom }}
            </h3>

            @if($r->zones->isNotEmpty())
            <p class="text-cyan-100/50 text-xs mb-3 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3 h-3"></i>
                {{ $r->zones->pluck('nom')->implode(', ') }}
            </p>
            @endif

            @if($r->description)
            <p class="text-cyan-100/70 text-sm leading-relaxed mb-4 line-clamp-2">
                {{ \Illuminate\Support\Str::limit($r->description, 120) }}
            </p>
            @endif

            {{-- Progression réelle du chantier --}}
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

            {{-- Une ou deux raisons maximum : suffisant pour expliquer, sans encombrer. --}}
            @if($reco->raisons)
            <ul class="space-y-1 mb-2">
                @foreach(array_slice($reco->raisons, 0, 2) as $raison)
                <li class="text-[11px] text-cyan-100/60 flex items-start gap-1.5">
                    <i data-lucide="check" class="w-3 h-3 mt-0.5 text-cyan-400 flex-shrink-0"></i>
                    <span>{{ $raison }}</span>
                </li>
                @endforeach
            </ul>
            @endif

            {{-- Budget restant : affiché seulement s'il est pertinent. --}}
            @if($reco->financable && $r->budget_disponible > 0)
            <p class="text-[11px] text-cyan-100/45 pt-2 border-t border-white/5">
                Reste à financer : <span class="font-bold text-white">{{ number_format($r->budget_disponible, 0, ',', ' ') }} DT</span>
            </p>
            @endif
        </article>
        @endforeach
    </div>
</section>
@endif
