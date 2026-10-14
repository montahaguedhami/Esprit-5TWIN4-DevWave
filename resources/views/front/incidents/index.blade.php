@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="citizen-incidents-page">
    <header class="citizen-incidents-heading">
        <div class="citizen-incidents-title">
            <span class="citizen-incidents-icon"><i data-lucide="clipboard-list"></i></span>
            <div>
                <p class="citizen-incidents-eyebrow">Portail citoyen <span>•</span> Suivi de vos demandes</p>
                <h1>Mes incidents</h1>
                <p class="citizen-incidents-description">Consultez l’avancement de vos signalements et les interventions associées.</p>
            </div>
        </div>
        <a href="{{ route('incidents.create') }}" class="citizen-incidents-action">
            <i data-lucide="plus"></i><span>Signaler un incident</span>
        </a>
    </header>

    @if(session('success'))
        <div role="status" class="citizen-incidents-success">
            <i data-lucide="circle-check"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="citizen-incident-list">
        @forelse($incidents as $incident)
            @php
                $incidentStatusKey = strtolower((string) $incident->statut);
                $incidentStatus = str_replace('_', '-', $incidentStatusKey);
                $incidentSeverity = strtolower((string) $incident->gravite);
                $incidentStatusLabel = ['resolu' => 'Résolu', 'en_cours' => 'En cours', 'en_attente' => 'En attente'][$incidentStatusKey] ?? ucfirst(str_replace('_', ' ', (string) $incident->statut));
                $incidentSeverityLabel = ['elevee' => 'Élevée', 'faible' => 'Faible', 'moyenne' => 'Moyenne'][$incidentSeverity] ?? ucfirst(str_replace('_', ' ', (string) $incident->gravite));
            @endphp
            <article class="citizen-incident-card" data-status="{{ $incidentStatus }}" data-severity="{{ $incidentSeverity }}">
                <span class="citizen-incident-type-icon"><i data-lucide="{{ strtolower((string) $incident->type) === 'contamination' ? 'flask-conical' : 'droplets' }}"></i></span>
                <div class="citizen-incident-content">
                    <div class="citizen-incident-card-top">
                        <div class="citizen-incident-name">
                            <span class="citizen-incident-type">{{ ucfirst((string) $incident->type) }}</span>
                            <a href="{{ route('incidents.show', $incident) }}" class="citizen-incident-title">{{ $incident->titre }}</a>
                        </div>
                        <time class="citizen-incident-date">
                            <i data-lucide="calendar-days"></i>
                            <span>Signalé le {{ optional($incident->date_signalement)->format('d/m/Y à H:i') }}</span>
                        </time>
                    </div>
                    <div class="citizen-incident-meta">
                        <span class="citizen-incident-badge citizen-incident-severity">
                            <i data-lucide="triangle-alert"></i>Gravité {{ $incidentSeverityLabel }}
                        </span>
                        <span class="citizen-incident-badge citizen-incident-status">
                            <span class="citizen-incident-status-dot"></span>{{ $incidentStatusLabel }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('incidents.show', $incident) }}" class="citizen-incident-open" aria-label="Consulter l’incident : {{ $incident->titre }}">
                    <i data-lucide="arrow-up-right"></i>
                </a>
            </article>
        @empty
            <div class="citizen-incidents-empty">
                <span class="citizen-incidents-empty-icon"><i data-lucide="clipboard-check"></i></span>
                <h2>Aucun incident signalé</h2>
                <p>Vos signalements apparaîtront ici pour vous permettre de suivre leur avancement.</p>
                <a href="{{ route('incidents.create') }}" class="citizen-incidents-action">
                    <i data-lucide="plus"></i><span>Signaler un incident</span>
                </a>
            </div>
        @endforelse
    </div>

    <div class="citizen-incidents-pagination">
        {{ $incidents->links() }}
    </div>
</div>
@endsection
