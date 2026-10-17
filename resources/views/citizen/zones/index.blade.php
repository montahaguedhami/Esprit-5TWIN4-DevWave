@extends('layouts.frontoffice')

@section('title', 'Zones desservies — AquaSecure')

@section('frontoffice-content')
<div class="citizen-directory">
    <header class="citizen-directory-heading">
        <span class="citizen-section-kicker">Réseau public</span>
        <h1>Zones desservies</h1>
        <p>Consultez les secteurs couverts par le réseau d'eau et les équipements qui les desservent.</p>
    </header>

    <form method="GET" action="{{ route('citizen.zones.index') }}" class="citizen-directory-filters citizen-zone-search">
        <label class="citizen-directory-search">
            <i data-lucide="search"></i>
            <span class="sr-only">Rechercher une zone</span>
            <input type="search" name="q" value="{{ $search }}" placeholder="Rechercher par nom ou localité">
        </label>
        <button type="submit" class="citizen-directory-submit">Rechercher</button>
        @if($search !== '')
            <a href="{{ route('citizen.zones.index') }}" class="citizen-directory-reset">Effacer</a>
        @endif
    </form>

    @if($zones->isEmpty())
        <div class="citizen-panel citizen-directory-empty">
            <span class="citizen-empty-icon"><i data-lucide="map-pin"></i></span>
            <h2>Aucune zone trouvée</h2>
            <p>Essayez un autre nom ou une autre localité.</p>
        </div>
    @else
        <div class="citizen-directory-grid">
            @foreach($zones as $zone)
                <article class="citizen-panel citizen-zone-card">
                    <div class="citizen-directory-card-top">
                        <span class="citizen-directory-icon"><i data-lucide="map"></i></span>
                        <span class="citizen-directory-status citizen-directory-status-active">
                            <span></span> Réseau actif
                        </span>
                    </div>
                    <h2>{{ $zone->nom }}</h2>
                    <p class="citizen-directory-location"><i data-lucide="map-pin"></i>{{ $zone->localisation }}</p>
                    @if($zone->description)
                        <p class="citizen-directory-description">{{ $zone->description }}</p>
                    @endif
                    <div class="citizen-zone-facts">
                        <span><strong>{{ $zone->infrastructures_count }}</strong> équipements</span>
                        <span><strong>{{ $zone->infrastructures_en_service_count }}</strong> en service</span>
                    </div>
                    <a href="{{ route('citizen.zones.show', $zone) }}" class="citizen-directory-link">
                        Consulter la zone <i data-lucide="arrow-right"></i>
                    </a>
                </article>
            @endforeach
        </div>
        <div class="citizen-directory-pagination">{{ $zones->links() }}</div>
    @endif
</div>
@endsection
