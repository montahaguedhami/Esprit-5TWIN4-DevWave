@extends('layouts.frontoffice')

@section('title', 'Équipements du réseau — AquaSecure')

@section('frontoffice-content')
<div class="citizen-directory">
    <header class="citizen-directory-heading">
        <span class="citizen-section-kicker">Réseau public</span>
        <h1>Équipements du réseau</h1>
        <p>Recherchez les principaux équipements et consultez leur état de service.</p>
    </header>

    <form method="GET" action="{{ route('citizen.infrastructures.index') }}" class="citizen-directory-filters">
        <label class="citizen-directory-search">
            <i data-lucide="search"></i>
            <span class="sr-only">Rechercher un équipement</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Nom ou localité">
        </label>
        <label>
            <span class="sr-only">Type d'équipement</span>
            <select name="type">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="sr-only">Zone desservie</span>
            <select name="zone_id">
                <option value="">Toutes les zones</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" @selected($filters['zone_id'] === (string) $zone->id)>{{ $zone->nom }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="sr-only">État de service</span>
            <select name="etat">
                <option value="">Tous les états</option>
                @foreach($etats as $etat)
                    <option value="{{ $etat->value }}" @selected($filters['etat'] === $etat->value)>{{ $etat->label() }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="citizen-directory-submit">Filtrer</button>
        @if(collect($filters)->contains(fn ($value) => $value !== ''))
            <a href="{{ route('citizen.infrastructures.index') }}" class="citizen-directory-reset">Effacer</a>
        @endif
    </form>

    @if($infrastructures->isEmpty())
        <div class="citizen-panel citizen-directory-empty">
            <span class="citizen-empty-icon"><i data-lucide="construction"></i></span>
            <h2>Aucun équipement trouvé</h2>
            <p>Modifiez vos critères de recherche ou consultez toutes les zones desservies.</p>
            <a href="{{ route('citizen.zones.index') }}" class="citizen-text-action">Consulter les zones <i data-lucide="arrow-right"></i></a>
        </div>
    @else
        <div class="citizen-directory-grid">
            @foreach($infrastructures as $infrastructure)
                <article class="citizen-panel citizen-zone-card">
                    <div class="citizen-directory-card-top">
                        <span class="citizen-directory-icon"><i data-lucide="construction"></i></span>
                        <span class="citizen-infrastructure-state {{ $infrastructure->etat === \App\Enums\InfrastructureEtat::EnService ? 'is-active' : ($infrastructure->etat === \App\Enums\InfrastructureEtat::EnMaintenance ? 'is-maintenance' : 'is-unavailable') }}">
                            {{ $infrastructure->etat->label() }}
                        </span>
                    </div>
                    <p class="citizen-section-kicker">{{ $infrastructure->type->label() }}</p>
                    <h2>{{ $infrastructure->nom }}</h2>
                    <p class="citizen-directory-location"><i data-lucide="map-pin"></i>{{ $infrastructure->localisation }} · {{ $infrastructure->zone->nom }}</p>
                    <a href="{{ route('citizen.infrastructures.show', $infrastructure) }}" class="citizen-directory-link">
                        Détails de service <i data-lucide="arrow-right"></i>
                    </a>
                </article>
            @endforeach
        </div>
        <div class="citizen-directory-pagination">{{ $infrastructures->links() }}</div>
    @endif
</div>
@endsection
