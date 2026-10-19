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
                <span class="citizen-section-kicker">Résultats vérifiés</span>
                <h2>Qualité de l’eau</h2>
                <p>Seules les mesures validées par un gestionnaire sont publiées.</p>
            </div>
        </header>
        @if($qualityPoints->isEmpty())
            <div class="citizen-empty-state"><h3>Aucun point de mesure répertorié</h3><p>Les résultats de qualité ne sont pas encore disponibles pour cette zone.</p></div>
        @else
            <div class="citizen-report-list">
                @foreach($qualityPoints as $entry)
                    @php($measurement = $entry['measurement'])
                    @php($status = $measurement?->status)
                    @php($statusLabel = match($status) { 'Compliant' => 'Conforme', 'Alert' => 'Vigilance', 'Non-Compliant' => 'Non conforme', default => $measurement ? 'État à confirmer' : 'Aucune donnée' })
                    @php($statusClass = match($status) { 'Compliant' => 'is-active', 'Alert' => 'is-maintenance', 'Non-Compliant' => 'is-unavailable', default => '' })
                    <article class="citizen-report-row">
                        <span class="citizen-report-marker"><i data-lucide="flask-conical"></i></span>
                        <span class="citizen-report-copy">
                            <span class="citizen-report-meta"><span class="citizen-report-id">{{ $entry['point']->code }}</span></span>
                            <strong>{{ $entry['point']->nom }}</strong>
                            <span class="citizen-report-location">{{ $entry['point']->adresse ?? $entry['point']->zone }}</span>
                            @if($entry['point']->infrastructure)
                                <span class="citizen-report-location">Infrastructure : {{ $entry['point']->infrastructure->nom }}</span>
                            @endif
                            @if($measurement)
                                <span class="citizen-report-location">Dernière analyse validée le {{ $measurement->date_mesure->format('d/m/Y à H:i') }}</span>
                                @if($entry['is_stale'])
                                    <span class="citizen-report-location text-amber-700">Résultat ancien : aucune mesure vérifiée depuis plus de 30 jours.</span>
                                @endif
                                <span class="citizen-report-location">pH {{ $measurement->ph }} · Turbidité {{ $measurement->turbidite }} NTU · Chlore {{ $measurement->chlore_residuel }} mg/L</span>
                                <span class="citizen-report-location">Plomb {{ $measurement->plomb }} ppb · Nitrates {{ $measurement->nitrates }} mg/L</span>
                            @else
                                <span class="citizen-report-location">Aucun résultat validé disponible pour le moment.</span>
                            @endif
                        </span>
                        <span class="citizen-infrastructure-state {{ $statusClass }}">{{ $statusLabel }}</span>
                    </article>
                    @if($status === 'Alert' || $status === 'Non-Compliant')
                        <p class="px-4 pb-3 text-sm text-amber-700">Un résultat nécessite une vigilance. Suivez les consignes communiquées par les autorités locales et signalez tout problème observé.</p>
                    @endif
                @endforeach
            </div>
        @endif
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
