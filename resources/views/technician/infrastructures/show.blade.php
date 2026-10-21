@extends('layouts.technician')

@section('title', $infrastructure->nom . ' — Fiche Technique Terrain')

@section('technician-content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- En-tête mobile avec bouton retour et actions --}}
    <div class="glass rounded-2xl p-4 sm:p-6 border border-white/10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <a href="{{ route('technician.infrastructures.index') }}"
                   class="w-10 h-10 rounded-xl glass border border-white/15 flex items-center justify-center text-cyan-300 hover:text-white transition-colors shrink-0 mt-1"
                   title="Retour aux ouvrages">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-xs font-mono font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-md border border-cyan-400/20">
                            {{ $infrastructure->reference ?? 'INF-'.$infrastructure->id }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold border {{ $infrastructure->etat?->badgeClass() ?? 'bg-slate-800 text-slate-300' }}">
                            {{ $infrastructure->etat?->label() ?? 'État inconnu' }}
                        </span>
                        @if($infrastructure->date_prochaine_maintenance && $infrastructure->date_prochaine_maintenance->isPast())
                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-rose-500/15 text-rose-300 border border-rose-500/30 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            Maintenance en retard
                        </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">
                        {{ $infrastructure->nom }}
                    </h1>
                    <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 inline text-cyan-400"></i>
                        {{ $infrastructure->zone?->nom ?? 'Zone non définie' }} · {{ $infrastructure->localisation }}
                    </p>
                </div>
            </div>

            {{-- Boutons d'action terrain --}}
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <a href="{{ route('technician.incidents.create', ['infrastructure_id' => $infrastructure->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600/90 hover:bg-rose-500 text-white shadow-md shadow-rose-950/30 transition-all">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>Signaler un problème</span>
                </a>

                @if($hasOpenIntervention)
                <button type="button"
                        onclick="document.getElementById('modal-changer-etat-technicien').classList.remove('hidden')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-950/30 transition-all">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Changer l'état</span>
                </button>
                @else
                <button type="button"
                        onclick="alert('Modification d\'état restreinte : vous devez avoir une intervention ouverte (Planifiée ou En cours) sur cette infrastructure pour pouvoir modifier son état opérationnel.');"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700 cursor-not-allowed"
                        title="Intervention ouverte requise">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Changer l'état</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <x-infrastructure.risk-technician-summary :risk="$risk" />

    {{-- Alerte si intervention en cours --}}
    @if($hasOpenIntervention && $openIntervention)
    <div class="glass rounded-2xl p-4 border border-amber-500/30 bg-amber-500/5">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-5 h-5 text-amber-400 animate-pulse"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">Intervention active en cours sur cet ouvrage</h2>
                    <p class="text-xs text-amber-200/70 mt-0.5">
                        Intervention #{{ $openIntervention->id }} ({{ $openIntervention->statut }}) du {{ $openIntervention->date?->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('technician.interventions.report', $openIntervention) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-amber-600 hover:bg-amber-500 text-white transition-all">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    <span>Remplir le rapport</span>
                </a>
                <a href="{{ route('technician.interventions.show', $openIntervention) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold glass text-amber-300 hover:text-white transition-all">
                    <span>Détails</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Grille principale : Données techniques + QR Code --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Colonne 1 & 2 : Fiche technique opérationnelle (SANS AUCUN COÛT) --}}
        <div class="md:col-span-2 space-y-6">
            <div class="glass rounded-2xl p-5 border border-white/10">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-cyan-400"></i>
                    Caractéristiques Techniques de l'Ouvrage
                </h2>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Type d'ouvrage</span>
                        <div class="flex items-center gap-2 text-white font-semibold">
                            <i data-lucide="{{ $infrastructure->type?->icon() ?? 'wrench' }}" class="w-4 h-4 text-cyan-400"></i>
                            {{ $infrastructure->type?->label() ?? 'Non défini' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block mb-1">Niveau de criticité</span>
                        <span class="font-semibold text-white">
                            {{ $infrastructure->niveau_criticite?->label() ?? 'Moyen' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block mb-1">Capacité nominale / Débit</span>
                        <span class="font-semibold text-white text-sm">
                            {{ $infrastructure->capacite ? number_format($infrastructure->capacite, 0, ',', ' ') . ' ' . $infrastructure->unite_capacite : 'Non précisée' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block mb-1">Fabricant / Marque</span>
                        <span class="font-semibold text-white">
                            {{ $infrastructure->fabricant ?? 'Non renseigné' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block mb-1">Date d'installation</span>
                        <span class="font-semibold text-white">
                            {{ $infrastructure->date_installation?->format('d/m/Y') ?? 'Non renseignée' }}
                            @if($infrastructure->duree_vie_annees)
                            <span class="text-cyan-100/40 text-[11px]">({{ $infrastructure->duree_vie_annees }} ans)</span>
                            @endif
                        </span>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block mb-1">Dernière maintenance effectuée</span>
                        <span class="font-semibold text-white">
                            {{ $infrastructure->date_derniere_maintenance?->format('d/m/Y') ?? 'Aucune' }}
                        </span>
                    </div>

                    <div class="col-span-2 pt-2 border-t border-white/5">
                        <span class="text-cyan-100/50 block mb-1">Prochaine maintenance planifiée</span>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold {{ $infrastructure->date_prochaine_maintenance && $infrastructure->date_prochaine_maintenance->isPast() ? 'text-rose-400 font-bold' : 'text-white' }}">
                                {{ $infrastructure->date_prochaine_maintenance?->format('d/m/Y') ?? 'Non programmée' }}
                            </span>
                            @if($infrastructure->date_prochaine_maintenance && $infrastructure->date_prochaine_maintenance->isPast())
                                <span class="text-[10px] text-rose-400">(Échue depuis {{ $infrastructure->date_prochaine_maintenance->diffForHumans(null, true) }})</span>
                            @endif
                        </div>
                    </div>

                    @if($infrastructure->latitude && $infrastructure->longitude)
                    <div class="col-span-2 pt-2 border-t border-white/5 flex items-center justify-between">
                        <div>
                            <span class="text-cyan-100/50 block text-[10px]">Coordonnées GPS WGS 84</span>
                            <span class="font-mono text-cyan-300 font-medium">
                                {{ number_format($infrastructure->latitude, 6) }}, {{ number_format($infrastructure->longitude, 6) }}
                            </span>
                        </div>
                        <a href="https://maps.google.com/?q={{ $infrastructure->latitude }},{{ $infrastructure->longitude }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                            <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                            <span>Itinéraire GPS</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Mes interventions sur cet ouvrage (Sans les coûts) --}}
            <div class="glass rounded-2xl p-5 border border-white/10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="wrench" class="w-4 h-4 text-cyan-400"></i>
                        Mes Interventions sur cet Ouvrage ({{ $interventions->count() }})
                    </h2>
                </div>

                @if($interventions->isEmpty())
                    <p class="text-xs text-cyan-100/50 italic py-2">Aucune intervention enregistrée pour votre profil sur cette infrastructure.</p>
                @else
                    <div class="space-y-3">
                        @foreach($interventions as $item)
                        <div class="p-3.5 rounded-xl bg-slate-900/60 border border-white/5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-mono font-bold text-cyan-400">#{{ $item->id }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $item->statut === 'Terminée' ? 'bg-emerald-500/15 text-emerald-300' : ($item->statut === 'En cours' ? 'bg-amber-500/15 text-amber-300' : 'bg-cyan-500/15 text-cyan-300') }}">
                                        {{ $item->statut }}
                                    </span>
                                    <span class="text-xs text-cyan-100/50">{{ $item->date?->format('d/m/Y') }}</span>
                                </div>
                                <p class="text-xs text-white line-clamp-2">{{ $item->description }}</p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @if($item->statut !== 'Terminée')
                                <a href="{{ route('technician.interventions.report', $item) }}"
                                   class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white transition-all">
                                    Rapport
                                </a>
                                @endif
                                <a href="{{ route('technician.interventions.show', $item) }}"
                                   class="px-2.5 py-1 rounded-lg text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                                    Voir
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Historique des changements d'état récents --}}
            <div class="glass rounded-2xl p-5 border border-white/10">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-cyan-400"></i>
                    Historique des États de l'Ouvrage
                </h2>

                <div class="space-y-3">
                    @forelse($infrastructure->historiques->take(5) as $hist)
                    <div class="flex items-start gap-3 text-xs border-l-2 border-cyan-500/40 pl-3 py-1">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-white">
                                    {{ $hist->ancien_etat ? ($hist->ancien_etat->label() ?? $hist->ancien_etat->value) . ' → ' : '' }}
                                    {{ $hist->nouvel_etat?->label() ?? $hist->nouvel_etat?->value ?? 'État inconnu' }}
                                </span>
                                <span class="text-cyan-100/40 text-[11px]">{{ $hist->created_at?->diffForHumans() }}</span>
                                <span class="text-[10px] text-cyan-300 font-mono">({{ $hist->user_name ?? 'Système' }})</span>
                            </div>
                            @if($hist->commentaire)
                            <p class="text-cyan-100/70 mt-1 italic">{{ $hist->commentaire }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-cyan-100/50 italic">Aucun changement d'état consigné.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Colonne 3 : QR Code Vectoriel & Outils Terrain --}}
        <div class="space-y-6">

            {{-- Carte QR Code --}}
            <div class="glass rounded-2xl p-5 border border-white/10 text-center">
                <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-300 mb-3 flex items-center justify-center gap-2">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                    QR Code de l'Ouvrage
                </h3>

                <div class="p-3 bg-white rounded-2xl inline-block shadow-lg mx-auto mb-3">
                    {!! $qrCodeSvg !!}
                </div>

                <p class="text-[11px] font-mono text-cyan-100/60 font-semibold mb-3">
                    {{ $infrastructure->reference ?? 'INF-'.$infrastructure->id }}
                </p>

                <p class="text-xs text-cyan-100/60 leading-relaxed mb-4">
                    Ce QR Code permet l'identification immédiate de la station sur le terrain lors des tournées d'inspection.
                </p>

                <button type="button"
                        onclick="window.print()"
                        class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Imprimer l'étiquette QR</span>
                </button>
            </div>

            {{-- Statut & Contact astreinte --}}
            <div class="glass rounded-2xl p-4 border border-white/10 space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="phone" class="w-4 h-4 text-rose-400"></i>
                    Assistance Dispatching
                </h3>
                <p class="text-xs text-cyan-100/60 leading-relaxed">
                    En cas de fuite majeure ou d'anomalie critique sur l'ouvrage, contactez immédiatement le centre d'exploitation.
                </p>
                <a href="tel:+21671000000"
                   class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold bg-rose-500/15 text-rose-300 hover:bg-rose-500/25 border border-rose-500/30 transition-all">
                    <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                    Appeler l'astreinte
                </a>
            </div>
        </div>
    </div>
</div>

{{-- MODALE DE CHANGEMENT D'ÉTAT RAPIDE DU TECHNICIEN (Avec commentaire obligatoire) --}}
@if($hasOpenIntervention)
<div id="modal-changer-etat-technicien" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="glass border border-white/20 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative animate-fade-in-up">
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/15 border border-amber-400/30 flex items-center justify-center">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-amber-400"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Changer l'état de l'ouvrage</h3>
                    <p class="text-xs text-cyan-100/50">Intervention ouverte active requise</p>
                </div>
            </div>
            <button type="button"
                    onclick="document.getElementById('modal-changer-etat-technicien').classList.add('hidden')"
                    class="text-cyan-100/40 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('technician.infrastructures.etat', $infrastructure) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="technicien_etat" class="block text-xs font-semibold text-cyan-100/70 mb-1">
                    Nouvel état opérationnel <span class="text-rose-400">*</span>
                </label>
                <select id="technicien_etat" name="etat" required class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs focus:ring-2 focus:ring-cyan-400">
                    @foreach($etats as $e)
                        <option value="{{ $e->value }}" @selected($infrastructure->etat?->value === $e->value)>
                            {{ $e->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="technicien_commentaire" class="block text-xs font-semibold text-cyan-100/70">
                        Commentaire explicatif <span class="text-rose-400">* (Obligatoire)</span>
                    </label>
                    <span id="tech-comment-counter" class="text-[11px] text-cyan-100/40 font-mono">0 / 500</span>
                </div>
                <textarea id="technicien_commentaire"
                          name="commentaire"
                          rows="4"
                          required
                          minlength="5"
                          maxlength="500"
                          placeholder="Ex : Vanne principale remplacée, test de pression validé, remise en eau effective..."
                          oninput="document.getElementById('tech-comment-counter').textContent = this.value.length + ' / 500'"
                          class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400"></textarea>
                <p class="text-[11px] text-cyan-100/50 mt-1">Ce motif sera automatiquement consigné dans l'historique officiel sous votre nom.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                <button type="button"
                        onclick="document.getElementById('modal-changer-etat-technicien').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-xs font-semibold glass text-cyan-100/60 hover:text-white transition-all">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-950/40 transition-all">
                    Enregistrer le changement
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
