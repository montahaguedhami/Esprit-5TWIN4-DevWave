@extends('layouts.frontoffice')

@section('title', 'Mon espace &mdash; AquaSecure')

@section('frontoffice-content')
<div class="citizen-dashboard">
    <section class="citizen-welcome">
        <div class="citizen-welcome-copy">
            <span class="citizen-eyebrow"><span></span> Portail citoyen AquaSecure</span>
            <h1>Bonjour, {{ explode(' ', trim($userName))[0] }}</h1>
            <p>Retrouvez vos signalements, les alertes du r&eacute;seau et les travaux pr&egrave;s de chez vous.</p>
        </div>
        <a href="{{ route('incidents.create') }}" class="citizen-primary-action">
            <i data-lucide="plus-circle"></i><span>Signaler un probl&egrave;me</span>
        </a>
    </section>

    <section class="citizen-stats" aria-label="R&eacute;sum&eacute; de votre activit&eacute;">
        @foreach([
            ['val' => $reportCount, 'label' => 'Mes signalements', 'icon' => 'file-text', 'tone' => 'cyan', 'sub' => 'Au total'],
            ['val' => $activeReportCount, 'label' => 'En traitement', 'icon' => 'clock-3', 'tone' => 'amber', 'sub' => 'Pas encore resolus'],
            ['val' => $unreadNotificationCount, 'label' => 'Notifications', 'icon' => 'bell', 'tone' => 'blue', 'sub' => 'Non lues'],
        ] as $kpi)
            <article class="citizen-stat-card citizen-tone-{{ $kpi['tone'] }}">
                <div class="citizen-stat-top">
                    <span class="citizen-stat-icon"><i data-lucide="{{ $kpi['icon'] }}"></i></span>
                    <span class="citizen-stat-sub">{{ $kpi['sub'] }}</span>
                </div>
                <p class="citizen-stat-value">{{ $kpi['val'] }}</p>
                <h2>{{ $kpi['label'] }}</h2>
            </article>
        @endforeach
    </section>

    <div class="citizen-content-grid">
        <section class="citizen-panel citizen-reports">
            <header class="citizen-panel-header">
                <div>
                    <span class="citizen-section-kicker">Mes d&eacute;marches</span>
                    <h2>Mes signalements</h2>
                    <p>&Eacute;tat des signalements que vous avez transmis</p>
                </div>
                <a href="{{ route('incidents.index') }}" class="citizen-text-action">Tout voir <i data-lucide="arrow-right"></i></a>
            </header>

            @forelse($reports as $report)
                @php
                    $normalizedStatus = \Illuminate\Support\Str::ascii(\Illuminate\Support\Str::lower((string) $report->statut));
                    $isResolved = str_contains($normalizedStatus, 'resolu') || str_contains($normalizedStatus, 'clotur');
                    $isInProgress = str_contains($normalizedStatus, 'cours') || str_contains($normalizedStatus, 'traitement');
                    $statusLabel = $isResolved ? 'Resolu' : ($isInProgress ? 'En cours' : 'En attente');
                    $statusClasses = $isResolved
                        ? 'bg-teal-500/15 text-teal-300'
                        : ($isInProgress ? 'bg-amber-500/15 text-amber-300' : 'bg-blue-500/15 text-blue-300');
                    $location = $report->localisation ?: ($report->zone?->nom ?? $report->infrastructure?->nom ?? 'Localisation non precisee');
                @endphp
                <a href="{{ route('incidents.show', $report) }}" class="citizen-report-row">
                    <span class="citizen-report-marker"><span class="{{ $isResolved ? 'bg-teal-400' : ($isInProgress ? 'bg-amber-400' : 'bg-blue-400') }}"></span></span>
                    <span class="citizen-report-copy">
                        <span class="citizen-report-meta">
                            <span class="citizen-report-id">#{{ $report->id }}</span>
                            <span class="citizen-status-pill {{ $statusClasses }}">{{ $statusLabel }}</span>
                        </span>
                        <strong>{{ $report->titre }}</strong>
                        <span class="citizen-report-location"><i data-lucide="map-pin"></i>{{ $location }} &middot; {{ $report->date_signalement?->format('d/m/Y') ?? $report->created_at?->format('d/m/Y') }}</span>
                    </span>
                    <i data-lucide="chevron-right" class="citizen-report-arrow"></i>
                </a>
            @empty
                <div class="citizen-empty-state">
                    <span class="citizen-empty-icon"><i data-lucide="file-plus-2"></i></span>
                    <h3>Aucun signalement pour le moment</h3>
                    <p>Vous pouvez signaler une fuite ou un probl&egrave;me d'eau dans votre quartier.</p>
                    <a href="{{ route('incidents.create') }}" class="citizen-primary-action">Cr&eacute;er un signalement</a>
                </div>
            @endforelse
        </section>

        <aside class="citizen-side-stack">
            <section class="citizen-panel citizen-alert-panel">
                <header class="citizen-panel-header citizen-panel-header-compact"><div><h2>Alertes du r&eacute;seau</h2></div></header>
                @forelse($alerts as $alert)
                    <article class="citizen-notification-row">
                        <span class="citizen-notification-icon bg-amber-500/15"><i data-lucide="triangle-alert" class="text-amber-400"></i></span>
                        <span>
                            <strong>{{ $alert->infrastructure?->nom ?? 'Information r&eacute;seau' }}</strong>
                            <span>{{ $alert->message }}</span>
                            @if($alert->infrastructure?->zone?->nom)<span>Secteur : {{ $alert->infrastructure->zone->nom }}</span>@endif
                        </span>
                    </article>
                @empty
                    <p class="text-sm text-cyan-100/60 px-4 py-3">Aucune alerte r&eacute;seau active pour le moment.</p>
                @endforelse
            </section>

            <section class="citizen-panel citizen-notifications">
                <header class="citizen-panel-header citizen-panel-header-compact">
                    <div><h2>Mes notifications</h2></div>
                    <a href="{{ route('citizen.notifications') }}" class="citizen-text-action">Tout voir <i data-lucide="arrow-right"></i></a>
                </header>
                @forelse($notifications as $notification)
                    @php
                        $notificationData = $notification->data ?? [];
                        $notificationTitle = $notificationData['action_title'] ?? $notificationData['incident_title'] ?? 'Mise a jour de votre compte';
                        $notificationMessage = $notificationData['message'] ?? 'Une nouvelle information est disponible.';
                    @endphp
                    <article class="citizen-notification-row">
                        <span class="citizen-notification-icon bg-cyan-500/15"><i data-lucide="bell" class="text-cyan-400"></i></span>
                        <span><strong>{{ $notificationTitle }}</strong><span>{{ $notificationMessage }}</span></span>
                        @if(!$notification->read_at)<i class="citizen-unread-dot" aria-label="Non lue"></i>@endif
                    </article>
                @empty
                    <p class="text-sm text-cyan-100/60 px-4 py-3">Aucune notification r&eacute;cente.</p>
                @endforelse
            </section>

            <section class="citizen-panel">
                <header class="citizen-panel-header citizen-panel-header-compact">
                    <div><h2>Travaux et annonces</h2><p>Interventions en cours ou prochainement pr&eacute;vues</p></div>
                    <a href="{{ route('citizen.travaux.index') }}" class="citizen-text-action">Tout voir <i data-lucide="arrow-right"></i></a>
                </header>
                @forelse($announcements as $announcement)
                    <a href="{{ route('citizen.travaux.show', $announcement) }}" class="citizen-report-row">
                        <span class="citizen-report-marker"><span class="bg-cyan-400"></span></span>
                        <span class="citizen-report-copy">
                            <strong>{{ $announcement->infrastructure?->nom ?? 'Intervention sur le r&eacute;seau' }}</strong>
                            <span class="citizen-report-location"><i data-lucide="calendar-days"></i>{{ $announcement->statut }} &middot; {{ $announcement->date?->format('d/m/Y') }}</span>
                        </span>
                        <i data-lucide="chevron-right" class="citizen-report-arrow"></i>
                    </a>
                @empty
                    <p class="text-sm text-cyan-100/60 px-4 py-3">Aucun travail annonc&eacute; actuellement.</p>
                @endforelse
            </section>

            <a href="{{ route('citizen.beaches.index') }}" class="citizen-panel citizen-text-action">
                <i data-lucide="waves"></i><span>Consulter les plages tunisiennes</span><i data-lucide="arrow-right"></i>
            </a>
        </aside>
    </div>
</div>
@endsection