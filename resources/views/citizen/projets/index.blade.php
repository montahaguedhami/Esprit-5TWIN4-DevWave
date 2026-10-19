@extends('layouts.frontoffice')

@php
    $title = 'Projets — AquaSecure';
    
    $statusStyle = [
        'planifie'    => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié'],
        'en_cours'    => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours'],
        'termine'     => ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Terminé'],
        'suspendu'    => ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>'Suspendu'],
        'annule'      => ['bg'=>'bg-red-500/15','text'=>'text-red-300','label'=>'Annulé'],
    ];
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Projets d'Infrastructure</h1>
        <p class="text-cyan-100/55 text-sm mt-1">Découvrez les projets d'amélioration du réseau d'eau dans votre région</p>
    </div>

    <!-- ── Recommandés pour vous ─────────────────────────────────────────── -->
    @include('citizen.recommandations._section', ['compact' => true])

    <!-- Filters & Search -->
    <div class="glass rounded-2xl p-4">
        <form method="GET" action="{{ route('citizen.projets.index') }}" class="flex flex-wrap gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Rechercher un projet..."
                       class="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors">
            </div>
            
            <!-- Status Filter -->
            <select name="statut" 
                    class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors">
                <option value="">Tous les statuts</option>
                <option value="planifie" {{ request('statut') === 'planifie' ? 'selected' : '' }}>Planifié</option>
                <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
            </select>
            
            <!-- Buttons -->
            <button type="submit" 
                    class="px-4 py-2 rounded-xl bg-cyan-500/15 text-cyan-300 hover:text-white font-semibold border border-cyan-400/25 transition-colors">
                <i data-lucide="search" class="w-4 h-4"></i>
            </button>
            @if(request('search') || request('statut'))
            <a href="{{ route('citizen.projets.index') }}" 
               class="px-4 py-2 rounded-xl bg-white/5 text-cyan-100/60 hover:text-white font-semibold transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </a>
            @endif
        </form>
    </div>

    <!-- Projects Grid -->
    @if($projets->count() > 0)
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($projets as $projet)
        @php $ss = $statusStyle[$projet->statut]; @endphp
        <article class="glass rounded-2xl p-5 hover-lift cursor-pointer" onclick="window.location='{{ route('citizen.projets.show', $projet) }}'">
            <!-- Status Badge + Favourite -->
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }}">
                    {{ $ss['label'] }}
                </span>
                <div class="flex items-center gap-1.5">
                    @if($projet->hasGeolocation())
                    <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400" title="Géolocalisé"></i>
                    @endif
                    {{-- ── Favori : s'arrête à lui-même, sinon il déclencherait ── --}}
                    {{--     le clic de la carte et naviguerait vers le détail.      --}}
                    <button type="button"
                            class="favori-toggle w-8 h-8 rounded-lg inline-flex items-center justify-center border transition-colors {{ ($favoris[$projet->id] ?? false) ? 'bg-amber-400/15 border-amber-400/40 text-amber-300' : 'bg-white/5 border-white/10 text-cyan-100/50 hover:text-white' }}"
                            data-projet-id="{{ $projet->id }}"
                            data-url="{{ route('citizen.projets.favori.basculer', $projet) }}"
                            data-etat="{{ ($favoris[$projet->id] ?? false) ? '1' : '0' }}"
                            aria-pressed="{{ ($favoris[$projet->id] ?? false) ? 'true' : 'false' }}"
                            aria-label="{{ ($favoris[$projet->id] ?? false) ? 'Retirer ce projet de vos favoris' : 'Ajouter ce projet à vos favoris' }}"
                            title="{{ ($favoris[$projet->id] ?? false) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                        <i data-lucide="star" class="w-4 h-4 {{ ($favoris[$projet->id] ?? false) ? 'fill-current' : '' }}"></i>
                    </button>
                </div>
            </div>

            <!-- Project Name -->
            <h2 class="text-white font-display font-bold text-base leading-snug mb-2">
                {{ $projet->nom }}
            </h2>

            <!-- Location -->
            @if($projet->adresse)
            <p class="text-cyan-100/50 text-xs mb-3 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3 h-3"></i>
                {{ $projet->adresse }}
            </p>
            @endif

            <!-- Description -->
            @if($projet->description)
            <p class="text-cyan-100/70 text-sm leading-relaxed mb-4 line-clamp-3">
                {{ $projet->description }}
            </p>
            @endif

            <!-- Progress & Budget Info -->
            <div class="mb-3 space-y-2">
                <!-- Progression Physique -->
                <div>
                    <div class="flex justify-between text-[11px] text-cyan-100/50 mb-1">
                        <span>Progression</span>
                        <span class="font-bold text-white">{{ $projet->progression }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                        @php
                            $progColor = $projet->progression >= 100 ? 'bg-green-400' : ($projet->progression >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                        @endphp
                        <div class="h-full rounded-full {{ $progColor }}" style="width: {{ min($projet->progression, 100) }}%"></div>
                    </div>
                </div>

                <!-- Financement (pas de montants détaillés pour citizen) -->
                <div>
                    <div class="flex justify-between text-[11px] text-cyan-100/50 mb-1">
                        <span>Financement</span>
                        <span class="font-bold text-white">{{ number_format($projet->pourcentage_finance, 0) }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                        @php
                            $percentage = min($projet->pourcentage_finance, 100);
                            $color = $percentage >= 100 ? 'bg-teal-400' : ($percentage >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                        @endphp
                        <div class="h-full rounded-full {{ $color }}" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Financial Stats (hidden for citizens per requirements) -->
            <div class="grid grid-cols-2 gap-2 mb-3">
                <div class="bg-white/[.03] rounded-lg px-2 py-1.5">
                    <p class="text-cyan-100/40 text-[10px]">Budget</p>
                    <p class="text-white font-semibold text-xs">{{ number_format($projet->budget / 1000, 0) }}K DT</p>
                </div>
                <div class="bg-white/[.03] rounded-lg px-2 py-1.5">
                    <p class="text-cyan-100/40 text-[10px]">Financé</p>
                    <p class="text-white font-semibold text-xs">{{ number_format($projet->total_finance / 1000, 0) }}K DT</p>
                </div>
            </div>

            <!-- View Details -->
            <div class="flex items-center justify-between pt-2 border-t border-white/5">
                <p class="text-[11px] text-cyan-100/45">
                    Début : {{ $projet->date_debut->format('m/Y') }}
                </p>
                <span class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                    Voir détails
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </span>
            </div>
        </article>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($projets->hasPages())
    <div class="glass rounded-2xl p-4">
        {{ $projets->links() }}
    </div>
    @endif
    @else
    <!-- Empty State -->
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="briefcase" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-white mb-2">Aucun projet trouvé</h3>
        <p class="text-cyan-100/60 mb-6">
            @if(request('search') || request('statut'))
                Aucun projet ne correspond à vos critères de recherche.
            @else
                Aucun projet n'est actuellement disponible.
            @endif
        </p>
        @if(request('search') || request('statut'))
        <a href="{{ route('citizen.projets.index') }}" 
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            Voir tous les projets
        </a>
        @endif
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // ── Favoris : bascule AJAX ────────────────────────────────────────────────
    // Un seul écouteur, délégué au document : les boutons ajoutés
    // dynamiquement (pagination, recommandations) fonctionnent sans être
    // ré-attachés. Aucune dépendance externe.
    (function () {
        const ENVOI = new Map();   // projetId → promesse, pour ignorer un double clic

        document.addEventListener('click', async function (evenement) {
            const bouton = evenement.target.closest('.favori-toggle');
            if (!bouton) { return; }

            // Stoppe la propagation : sur une carte, le clic ouvre le détail.
            evenement.preventDefault();
            evenement.stopPropagation();

            const projetId = bouton.dataset.projetId;
            if (ENVOI.has(projetId)) { return; }   // requête déjà en vol
            ENVOI.set(projetId, true);

            const etaitFavori = bouton.dataset.etat === '1';

            try {
                // PATCH = bascule : le serveur décide lui-même de l'état final,
                // ce qui évite qu'un état affiché périmécré déclenche deux fois
                // le même effet.
                const reponse = await fetch(bouton.dataset.url, {
                    method: 'PATCH',
                    headers: {
                        'Accept':       'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (!reponse.ok) { throw new Error('HTTP ' + reponse.status); }

                const donnees = await reponse.json();

                appliquerEtat(bouton, donnees.favori);
                notifier(donnees.message, donnees.favori);

                // Sur la page « Mes favoris », un retrait retire la carte.
                if (bouton.dataset.retour === 'page' && !donnees.favori) {
                    const carte = bouton.closest('article');
                    if (carte) {
                        carte.style.transition = 'opacity .2s';
                        carte.style.opacity = '0';
                        setTimeout(() => window.location.reload(), 220);
                        return;
                    }
                }
            } catch (erreur) {
                // L'état n'a pas changé côté serveur : on restaure l'affichage
                // plutôt que de laisser un bouton mentir.
                appliquerEtat(bouton, etaitFavori);
                notifier('Action impossible pour le moment.', false);
            } finally {
                ENVOI.delete(projetId);
            }
        });

        function appliquerEtat(bouton, estFavori) {
            bouton.dataset.etat = estFavori ? '1' : '0';
            bouton.setAttribute('aria-pressed', estFavori ? 'true' : 'false');

            bouton.classList.toggle('bg-amber-400/15', estFavori);
            bouton.classList.toggle('border-amber-400/40', estFavori);
            bouton.classList.toggle('text-amber-300', estFavori);
            bouton.classList.toggle('bg-white/5',      !estFavori);
            bouton.classList.toggle('border-white/10', !estFavori);
            bouton.classList.toggle('text-cyan-100/50',!estFavori);

            const etoile = bouton.querySelector('[data-lucide="star"]');
            if (etoile) { etoile.classList.toggle('fill-current', estFavori); }
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        }

        function notifier(message, succes) {
            if (!message) { return; }
            const zone = document.getElementById('fo-flash');
            if (!zone) { return; }
            zone.innerHTML =
                '<div class="rounded-xl px-4 py-3 text-sm font-semibold ' +
                (succes ? 'bg-teal-500/15 text-teal-300 border border-teal-400/25'
                        : 'bg-amber-500/15 text-amber-300 border border-amber-400/25') +
                '">' + message + '</div>';
            setTimeout(() => { zone.innerHTML = ''; }, 3200);
        }
    })();

    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>
@endpush
