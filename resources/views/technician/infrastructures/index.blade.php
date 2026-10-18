@extends('layouts.manager')

@section('title', 'Mes Ouvrages Assignés — Technicien')

@section('manager-content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- En-tête mobile-first --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 text-xs font-semibold mb-2">
                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                Périmètre Terrain · {{ $technicien->nom }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Mes Ouvrages Assignés</h1>
            <p class="text-cyan-100/60 text-sm mt-0.5">Infrastructures reliées à vos interventions de maintenance terrain.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('technician.scan') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-900/30 transition-all">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span>Scanner QR Code</span>
            </a>
            <a href="{{ route('technician.tournee') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition-all">
                <i data-lucide="map" class="w-4 h-4 text-cyan-400"></i>
                <span>Tournée du jour</span>
            </a>
        </div>
    </div>

    {{-- 4 Métriques de terrain (Sans aucun coût) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="glass rounded-2xl p-4 border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs text-cyan-100/60 font-medium">Total Ouvrages</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                    <i data-lucide="layers" class="w-4 h-4 text-cyan-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold font-display text-white mt-2">{{ $stats['total'] }}</p>
        </div>

        <div class="glass rounded-2xl p-4 border border-emerald-500/20">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-300 font-medium">En service</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold font-display text-white mt-2">{{ $stats['en_service'] }}</p>
        </div>

        <div class="glass rounded-2xl p-4 border border-amber-500/20">
            <div class="flex items-center justify-between">
                <span class="text-xs text-amber-300 font-medium">En maintenance</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <i data-lucide="tool" class="w-4 h-4 text-amber-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold font-display text-white mt-2">{{ $stats['en_maintenance'] }}</p>
        </div>

        <div class="glass rounded-2xl p-4 border border-rose-500/20">
            <div class="flex items-center justify-between">
                <span class="text-xs text-rose-300 font-medium">Hors service</span>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold font-display text-white mt-2">{{ $stats['hors_service'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Formulaire de filtres mobile-friendly --}}
    <form method="GET" action="{{ route('technician.infrastructures.index') }}" class="glass rounded-2xl p-4 border border-white/10 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-cyan-100/40"></i>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Rechercher nom, référence, lieu..."
                       class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900/60 border border-white/10 text-white placeholder-cyan-100/40 text-xs focus:ring-2 focus:ring-cyan-400/50 focus:border-cyan-400 transition-all">
            </div>

            <div class="sm:col-span-3">
                <select name="etat" class="w-full px-3 py-2 rounded-xl bg-slate-900/60 border border-white/10 text-white text-xs focus:ring-2 focus:ring-cyan-400/50">
                    <option value="">Tous les états</option>
                    @foreach($etats as $e)
                        <option value="{{ $e->value }}" @selected(request('etat') === $e->value)>{{ $e->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-900/60 border border-white/10 text-white text-xs focus:ring-2 focus:ring-cyan-400/50">
                    <option value="">Tous les types</option>
                    @foreach($types as $t)
                        <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition-all">
                    Filtrer
                </button>
                @if(request()->hasAny(['q', 'etat', 'type', 'open_only']))
                <a href="{{ route('technician.infrastructures.index') }}" class="p-2 rounded-xl glass text-cyan-300 hover:text-white text-xs transition-colors" title="Réinitialiser">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1 text-xs text-cyan-100/60">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="open_only" value="1" @checked(request()->boolean('open_only')) onchange="this.form.submit()" class="rounded border-white/20 bg-slate-900 text-cyan-500 focus:ring-cyan-400">
                <span>Afficher uniquement les ouvrages avec une intervention ouverte (Planifiée ou En cours)</span>
            </label>
        </div>
    </form>

    {{-- Liste des infrastructures du technicien --}}
    @if($infrastructures->isEmpty())
        <div class="glass rounded-2xl p-10 text-center border border-white/10">
            <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="wrench" class="w-7 h-7 text-cyan-400"></i>
            </div>
            <h3 class="text-lg font-bold text-white">Aucun ouvrage trouvé dans votre périmètre</h3>
            <p class="text-sm text-cyan-100/60 max-w-md mx-auto mt-1">
                Aucune infrastructure ne correspond à vos filtres actuels, ou aucune intervention ne vous a encore été assignée sur ces critères.
            </p>
            <div class="mt-4">
                <a href="{{ route('technician.infrastructures.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-cyan-400 hover:text-cyan-300">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Réinitialiser les filtres
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($infrastructures as $infra)
            @php
                $hasOpen = $infra->interventions->contains(fn($i) => in_array($i->statut, ['Planifiée', 'En cours']));
            @endphp
            <div class="glass rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all flex flex-col justify-between group">
                <div>
                    {{-- Ligne 1 : Badges et Référence --}}
                    <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $infra->etat?->badgeClass() ?? 'bg-slate-800 text-slate-300 border-slate-700' }}">
                                {{ $infra->etat?->label() ?? 'Inconnu' }}
                            </span>
                            @if($hasOpen)
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-400/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                Intervention en cours
                            </span>
                            @endif
                        </div>
                        <span class="text-[11px] font-mono text-cyan-100/50 font-semibold">{{ $infra->reference ?? 'INF-'.$infra->id }}</span>
                    </div>

                    {{-- Nom & Type --}}
                    <div class="flex items-start gap-3 mt-1">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                            <i data-lucide="{{ $infra->type?->icon() ?? 'wrench' }}" class="w-5 h-5 text-cyan-400"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white group-hover:text-cyan-300 transition-colors">
                                {{ $infra->nom }}
                            </h2>
                            <p class="text-xs text-cyan-100/50 mt-0.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 inline text-cyan-400"></i>
                                {{ $infra->zone?->nom ?? 'Zone non définie' }} · {{ $infra->localisation }}
                            </p>
                        </div>
                    </div>

                    {{-- Caractéristiques techniques terrain (Sans coût) --}}
                    <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-white/5 text-xs text-cyan-100/70">
                        <div>
                            <span class="text-cyan-100/40 block text-[10px]">Capacité / Débit :</span>
                            <span class="font-medium text-white">{{ $infra->capacite ? number_format($infra->capacite, 0, ',', ' ') . ' ' . $infra->unite_capacite : 'Non précisée' }}</span>
                        </div>
                        <div>
                            <span class="text-cyan-100/40 block text-[10px]">Dernière maintenance :</span>
                            <span class="font-medium {{ $infra->date_derniere_maintenance ? 'text-white' : 'text-cyan-100/40' }}">
                                {{ $infra->date_derniere_maintenance?->format('d/m/Y') ?? 'Aucune' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Boutons d'action rapides --}}
                <div class="flex items-center justify-between gap-2 mt-5 pt-3 border-t border-white/5">
                    <span class="text-[11px] text-cyan-100/50">
                        <i data-lucide="calendar" class="w-3 h-3 inline"></i>
                        {{ $infra->interventions->count() }} intervention(s)
                    </span>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('technician.infrastructures.show', $infra) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 hover:text-white border border-cyan-400/30 transition-all">
                            <span>Fiche technique</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $infrastructures->links() }}
        </div>
    @endif

</div>
@endsection
