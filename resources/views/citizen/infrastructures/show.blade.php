@extends('layouts.frontoffice')

@section('title', $infrastructure->nom.' — AquaSecure')

@section('frontoffice-content')
<div class="citizen-directory citizen-directory-detail">
    <a href="{{ route('citizen.infrastructures.index') }}" class="citizen-directory-back"><i data-lucide="arrow-left"></i> Tous les équipements</a>

    <section class="citizen-panel citizen-directory-hero">
        <div class="citizen-directory-card-top">
            <span class="citizen-directory-icon"><i data-lucide="construction"></i></span>
            <span class="citizen-infrastructure-state {{ $infrastructure->etat === \App\Enums\InfrastructureEtat::EnService ? 'is-active' : ($infrastructure->etat === \App\Enums\InfrastructureEtat::EnMaintenance ? 'is-maintenance' : 'is-unavailable') }}">
                {{ $infrastructure->etat->label() }}
            </span>
        </div>
        <span class="citizen-section-kicker">{{ $infrastructure->type->label() }}</span>
        <h1>{{ $infrastructure->nom }}</h1>
        <p class="citizen-directory-location"><i data-lucide="map-pin"></i>{{ $infrastructure->localisation }}</p>

        <dl class="citizen-infrastructure-details">
            <div><dt>Zone desservie</dt><dd><a href="{{ route('citizen.zones.show', $infrastructure->zone) }}">{{ $infrastructure->zone->nom }}</a></dd></div>
            <div><dt>État de service</dt><dd>{{ $infrastructure->etat->label() }}</dd></div>
        </dl>

        @if($infrastructure->etat !== \App\Enums\InfrastructureEtat::EnService)
            <p class="citizen-service-notice">
                <i data-lucide="info"></i>
                Cet équipement n'est pas indiqué comme étant en service. Pour signaler un problème dans votre quartier, contactez les équipes via le formulaire.
            </p>
        @endif
        <a href="{{ route('incidents.create') }}" class="citizen-primary-action"><i data-lucide="triangle-alert"></i><span>Signaler un problème</span></a>
    </section>
</div>
@endsection
