@extends('layouts.manager')

@section('title', 'Intervention #' . $intervention->id . ' — Technicien')

@section('manager-content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- En-tête avec bouton retour et statut --}}
    <div class="glass rounded-2xl p-5 border border-white/10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <a href="{{ route('technician.interventions.index') }}"
                   class="w-10 h-10 rounded-xl glass border border-white/15 flex items-center justify-center text-cyan-300 hover:text-white transition-colors shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-xs font-mono font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-md border border-cyan-400/20">
                            Intervention #{{ $intervention->id }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $intervention->statut === 'Terminée' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/30' : ($intervention->statut === 'En cours' ? 'bg-amber-500/15 text-amber-300 border border-amber-400/30' : 'bg-cyan-500/15 text-cyan-300 border border-cyan-400/30') }}">
                            {{ $intervention->statut }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-display text-white">
                        {{ $intervention->infrastructure?->nom ?? 'Mission de maintenance' }}
                    </h1>
                    <p class="text-cyan-100/60 text-xs mt-0.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 inline text-cyan-400"></i>
                        Date prévue : {{ $intervention->date?->format('d/m/Y') }} · Technicien : {{ $technicien->nom }}
                    </p>
                </div>
            </div>

            {{-- Actions directes selon l'état --}}
            <div class="flex items-center gap-2 flex-wrap">
                @if($intervention->statut === 'Planifiée')
                <form method="POST" action="{{ route('technician.interventions.start', $intervention) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md transition-all flex items-center gap-2">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        <span>Démarrer l'intervention</span>
                    </button>
                </form>
                @endif

                @if($intervention->statut !== 'Terminée')
                <a href="{{ route('technician.interventions.report', $intervention) }}"
                   class="px-4 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md transition-all flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Remplir le rapport</span>
                </a>
                @endif

                @if($intervention->infrastructure)
                <a href="{{ route('technician.incidents.create', ['infrastructure_id' => $intervention->infrastructure->id]) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-600/80 hover:bg-rose-500 text-white transition-all flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                    <span>Signaler problème</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Détails de l'intervention & Fiche ouvrage liée --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Descriptif et Rapport --}}
        <div class="md:col-span-2 space-y-6">
            <div class="glass rounded-2xl p-5 border border-white/10 space-y-3">
                <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="align-left" class="w-4 h-4 text-cyan-400"></i>
                    Ordre de Mission & Descriptif
                </h2>
                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 text-xs text-cyan-100/90 whitespace-pre-line leading-relaxed">
                    {{ $intervention->description ?: 'Aucune consigne spécifique renseignée pour cette intervention.' }}
                </div>
            </div>

            @if($intervention->statut === 'Terminée')
            <div class="glass rounded-2xl p-5 border border-emerald-500/20 bg-emerald-500/5 space-y-2">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Mission Clôturée
                </div>
                <p class="text-xs text-cyan-100/70">
                    Cette intervention a été validée avec succès sur le terrain. L'état opérationnel et la date de dernière maintenance de l'ouvrage ont été mis à jour.
                </p>
            </div>
            @endif
        </div>

        {{-- Carte latérale Ouvrage lié (SANS COÛT) --}}
        <div class="space-y-6">
            @if($intervention->infrastructure)
            @php($infra = $intervention->infrastructure)
            <div class="glass rounded-2xl p-5 border border-white/10 space-y-4">
                <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="wrench" class="w-4 h-4 text-cyan-400"></i>
                    Ouvrage Rattaché
                </h2>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-cyan-100/50 block text-[11px]">Nom :</span>
                        <span class="font-bold text-white text-sm">{{ $infra->nom }}</span>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block text-[11px]">Type & État :</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="font-medium text-white">{{ $infra->type?->label() }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $infra->etat?->badgeClass() }}">
                                {{ $infra->etat?->label() }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-cyan-100/50 block text-[11px]">Localisation :</span>
                        <span class="text-white">{{ $infra->zone?->nom }} · {{ $infra->localisation }}</span>
                    </div>

                    @if($infra->latitude && $infra->longitude)
                    <div class="pt-2 border-t border-white/5">
                        <a href="https://maps.google.com/?q={{ $infra->latitude }},{{ $infra->longitude }}"
                           target="_blank"
                           class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                            <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                            <span>Ouvrir GPS / Itinéraire</span>
                        </a>
                    </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-white/10">
                    <a href="{{ route('technician.infrastructures.show', $infra) }}"
                       class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white transition-all">
                        <span>Consulter la Fiche Technique</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
