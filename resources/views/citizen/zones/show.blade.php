@extends('layouts.frontoffice')

@section('title', $zone->nom.' — AquaSecure')

@section('frontoffice-content')
<div class="citizen-directory citizen-directory-detail">
    <a href="{{ route('citizen.zones.index') }}" class="citizen-directory-back"><i data-lucide="arrow-left"></i> Toutes les zones</a>

    <section class="citizen-panel citizen-directory-hero">
        <div class="citizen-directory-card-top">
            <span class="citizen-directory-icon"><i data-lucide="map"></i></span>
            <span class="citizen-directory-status citizen-directory-status-active"><span></span> Réseau actif</span>
        </div>
        <h1>{{ $zone->nom }}</h1>
        <p class="citizen-directory-location"><i data-lucide="map-pin"></i>{{ $zone->localisation }}</p>
        @if($zone->description)
            <p class="citizen-directory-description">{{ $zone->description }}</p>
        @endif

        <div class="citizen-zone-summary">
            <div><span>Équipements recensés</span><strong>{{ $zone->infrastructures_count }}</strong></div>
            <div><span>Équipements en service</span><strong>{{ $zone->infrastructures_en_service_count }}</strong></div>
            <div><span>Signalements actifs</span><strong>{{ $openIncidentsCount }}</strong></div>
        </div>
        <a href="{{ route('incidents.create') }}" class="citizen-primary-action">
            <i data-lucide="triangle-alert"></i><span>Signaler un problème dans cette zone</span>
        </a>
    </section>

    <section class="citizen-panel">
        <header class="citizen-panel-header">
            <div>
                <span class="citizen-section-kicker">Équipements publics</span>
                <h2>Infrastructures de la zone</h2>
                <p>État de service et emplacement général des équipements.</p>
            </div>
        </header>
        @if($infrastructures->isEmpty())
            <div class="citizen-empty-state">
                <span class="citizen-empty-icon"><i data-lucide="construction"></i></span>
                <h3>Aucun équipement répertorié</h3>
                <p>Les équipements de cette zone ne sont pas encore disponibles dans l'annuaire.</p>
            </div>
        @else
            <div class="citizen-report-list">
                @foreach($infrastructures as $infrastructure)
                    <a href="{{ route('citizen.infrastructures.show', $infrastructure) }}" class="citizen-report-row">
                        <span class="citizen-report-marker"><i data-lucide="construction"></i></span>
                        <span class="citizen-report-copy">
                            <span class="citizen-report-meta"><span class="citizen-report-id">{{ $infrastructure->type->label() }}</span></span>
                            <strong>{{ $infrastructure->nom }}</strong>
                            <span class="citizen-report-location"><i data-lucide="map-pin"></i>{{ $infrastructure->localisation }}</span>
                        </span>
                        <span class="citizen-infrastructure-state {{ $infrastructure->etat === \App\Enums\InfrastructureEtat::EnService ? 'is-active' : ($infrastructure->etat === \App\Enums\InfrastructureEtat::EnMaintenance ? 'is-maintenance' : 'is-unavailable') }}">
                            {{ $infrastructure->etat->label() }}
                        </span>
                        <i data-lucide="chevron-right" class="citizen-report-arrow"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
