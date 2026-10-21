@extends('layouts.frontoffice')

@section('title', 'Mon espace — AquaSecure')

@php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Yassine Hamdi', 'email' => 'citoyen@aquasecure.tn']);
    $reports      = PlaceholderData::citizenReports();
    $notifs       = PlaceholderData::citizenNotifications();
    $invoices     = PlaceholderData::citizenInvoices();
    $zones        = PlaceholderData::zones();

    $firstName    = explode(' ', $user['name'])[0];
    $activeRep    = count(array_filter($reports, fn($r) => $r['status'] === 'in_progress'));
    $resolvedRep  = count(array_filter($reports, fn($r) => $r['status'] === 'resolved'));
    $pendingRep   = count(array_filter($reports, fn($r) => $r['status'] === 'pending'));
    $unreadNotifs = count(array_filter($notifs, fn($n) => !$n['read']));

    $statusStyle = [
        'in_progress' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400 animate-pulse','label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15', 'text'=>'text-teal-300', 'dot'=>'bg-teal-400', 'label'=>'Résolu'],
        'pending'     => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400', 'label'=>'En attente'],
    ];
@endphp

@section('frontoffice-content')
<div class="citizen-dashboard">
    <section class="citizen-welcome">
        <div class="citizen-welcome-copy">
            <span class="citizen-eyebrow"><span></span> Portail citoyen AquaSecure</span>
            <h1>Bonjour, {{ $firstName }} 👋</h1>
            <p>Suivez vos signalements, consultez l'état du réseau d'eau et retrouvez vos factures depuis votre espace.</p>
        </div>
        <a href="{{ route('incidents.create') }}" class="citizen-primary-action">
            <i data-lucide="plus-circle"></i>
            <span>Signaler un problème</span>
        </a>
    </section>

    <section class="citizen-stats" aria-label="Résumé de votre activité">
        @foreach([
            ['val'=>count($reports), 'label'=>'Mes signalements', 'icon'=>'file-text',   'tone'=>'cyan',  'sub'=>'Total déposés'],
            ['val'=>$activeRep,      'label'=>'En cours',         'icon'=>'loader',      'tone'=>'amber', 'sub'=>'En traitement'],
            ['val'=>$resolvedRep,    'label'=>'Résolus',          'icon'=>'check-circle','tone'=>'teal',  'sub'=>'Interventions closes'],
            ['val'=>$unreadNotifs,   'label'=>'Notifications',    'icon'=>'bell',        'tone'=>'blue',  'sub'=>'Non lues'],
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
                    <span class="citizen-section-kicker">Suivi du réseau</span>
                    <h2>Mes signalements</h2>
                    <p>Derniers rapports transmis aux équipes techniques</p>
                </div>
                <a href="{{ route('incidents.create') }}" class="citizen-text-action">
                    <i data-lucide="plus"></i><span>Nouveau</span>
                </a>
            </header>

            @if(count($reports) === 0)
            <div class="citizen-empty-state">
                <span class="citizen-empty-icon"><i data-lucide="file-plus-2"></i></span>
                <h3>Aucun signalement pour le moment</h3>
                <p>Signalez une fuite ou une baisse de pression dans votre quartier pour la transmettre aux équipes.</p>
                <a href="{{ route('incidents.create') }}" class="citizen-primary-action">Créer un signalement</a>
            </div>
            @else
            <div class="citizen-report-list">
                @foreach($reports as $rep)
                @php $ss = $statusStyle[$rep['status']] ?? $statusStyle['pending']; @endphp
                <a href="{{ route('citizen.reports.show', $rep['id']) }}" class="citizen-report-row">
                    <span class="citizen-report-marker"><span class="{{ $ss['dot'] }}"></span></span>
                    <span class="citizen-report-copy">
                        <span class="citizen-report-meta">
                            <span class="citizen-report-id">{{ $rep['id'] }}</span>
                            <span class="citizen-status-pill {{ $ss['bg'] }} {{ $ss['text'] }}">{{ $ss['label'] }}</span>
                        </span>
                        <strong>{{ $rep['type'] }}</strong>
                        <span class="citizen-report-location"><i data-lucide="map-pin"></i>{{ $rep['zone'] }} · {{ $rep['created_at'] }}</span>
                    </span>
                    <i data-lucide="chevron-right" class="citizen-report-arrow"></i>
                </a>
                @endforeach
            </div>
            @endif
        </section>

        <aside class="citizen-side-stack">
            @php $alertZone = array_values(array_filter($zones, fn($z)=>$z['status']==='critical'))[0] ?? null; @endphp
            @if($alertZone)
            <section class="citizen-panel citizen-alert-panel">
                <header class="citizen-alert-heading">
                    <span><i data-lucide="triangle-alert"></i> Alerte réseau locale</span>
                    <span class="citizen-alert-status"><span></span> En cours</span>
                </header>
                <h2>{{ $alertZone['name'] }}</h2>
                <p>Perturbation de pression détectée dans le secteur. Des équipes de maintenance sont en cours d'intervention.</p>
                <footer>
                    <span>Qualité <strong>{{ $alertZone['quality'] }}%</strong></span>
                    <span>Pression <strong>{{ $alertZone['pressure'] }} bar</strong></span>
                </footer>
            </section>
            @endif

            <section class="citizen-panel citizen-notifications">
                <header class="citizen-panel-header citizen-panel-header-compact">
                    <div><h2>Dernières notifications</h2></div>
                    <a href="{{ route('citizen.notifications') }}" class="citizen-text-action">Tout voir <i data-lucide="arrow-right"></i></a>
                </header>
                <div class="citizen-notification-list">
                    @foreach(array_slice($notifs, 0, 3) as $notif)
                    @php
                        $nb = match($notif['color']) {
                            'amber'   => ['bg'=>'bg-amber-500/15','ic'=>'text-amber-400'],
                            'emerald' => ['bg'=>'bg-teal-500/15', 'ic'=>'text-teal-400'],
                            'blue'    => ['bg'=>'bg-blue-500/15', 'ic'=>'text-blue-400'],
                            'purple'  => ['bg'=>'bg-violet-500/15','ic'=>'text-violet-400'],
                            default   => ['bg'=>'bg-cyan-500/15', 'ic'=>'text-cyan-400'],
                        };
                    @endphp
                    <article class="citizen-notification-row">
                        <span class="citizen-notification-icon {{ $nb['bg'] }}"><i data-lucide="{{ $notif['icon'] }}" class="{{ $nb['ic'] }}"></i></span>
                        <span><strong>{{ $notif['title'] }}</strong><span>{{ $notif['message'] }}</span></span>
                        @if(!$notif['read'])<i class="citizen-unread-dot" aria-label="Non lue"></i>@endif
                    </article>
                    @endforeach
                </div>
            </section>

            <section class="citizen-panel citizen-consumption">
                <header class="citizen-panel-header citizen-panel-header-compact">
                    <div><h2>Ma consommation</h2><p>Moyenne mensuelle</p></div>
                    <a href="{{ route('citizen.invoices.index') }}" class="citizen-text-action">Factures <i data-lucide="arrow-right"></i></a>
                </header>
                <div class="citizen-consumption-value">
                    <span class="citizen-consumption-icon"><i data-lucide="droplet"></i></span>
                    <strong>19.6 <small>m³</small></strong>
                </div>
            </section>
        </aside>
        <section class="citizen-panel mt-6">
            <header class="citizen-panel-header">
                <div>
                    <span class="citizen-section-kicker">Information locale</span>
                    <h2>Plages tunisiennes</h2>
                    <p>Recherchez une plage et filtrez les résultats par gouvernorat.</p>
                </div>
                <a href="{{ route('citizen.beaches.index') }}" class="citizen-text-action">
                    <i data-lucide="waves"></i><span>Consulter les plages</span>
                </a>
            </header>
        </section>    </div>
</div>
@endsection
