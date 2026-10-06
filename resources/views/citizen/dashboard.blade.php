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
        'in_progress' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400','label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15', 'text'=>'text-teal-300', 'dot'=>'bg-teal-400', 'label'=>'Résolu'],
        'pending'     => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400', 'label'=>'En attente'],
    ];
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    {{-- ══ HEADER ══════════════════════════════════════════════ --}}
    <div class="glass rounded-2xl p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">
                    Bonjour, {{ $firstName }} 👋
                </h1>
                <p class="text-cyan-100/55 text-sm mt-1">{{ $user['email'] }}</p>
                <p class="text-cyan-100/65 text-sm mt-2 max-w-md">
                    Suivez vos signalements, consultez l'état de votre réseau et restez informé.
                </p>
            </div>
            <a href="{{ route('citizen.reports.create') }}"
               class="shrink-0 flex items-center gap-2 bg-gradient-to-r from-cyan-500 to-blue-600
                      hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-semibold
                      px-4 py-2.5 rounded-xl transition-all hover-lift shadow-lg shadow-cyan-500/20">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Signaler</span>
            </a>
        </div>
    </div>

    {{-- ══ 4 KPI CARDS ════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['val'=>count($reports), 'label'=>'Mes signalements', 'icon'=>'file-text',    'bg'=>'bg-cyan-500/10',   'ic'=>'text-cyan-400',   'sub'=>'total'],
            ['val'=>$activeRep,      'label'=>'En cours',          'icon'=>'loader',        'bg'=>'bg-amber-500/10',  'ic'=>'text-amber-400',  'sub'=>'en traitement'],
            ['val'=>$resolvedRep,    'label'=>'Résolus',           'icon'=>'check-circle',  'bg'=>'bg-teal-500/10',   'ic'=>'text-teal-400',   'sub'=>'clôturés'],
            ['val'=>$unreadNotifs,   'label'=>'Notifications',      'icon'=>'bell',          'bg'=>'bg-blue-500/10',   'ic'=>'text-blue-400',   'sub'=>'non lues'],
        ] as $kpi)
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl {{ $kpi['bg'] }} flex items-center justify-center mb-3">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-5 h-5 {{ $kpi['ic'] }}"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white">{{ $kpi['val'] }}</p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">{{ $kpi['label'] }}</p>
            <p class="text-[10px] text-cyan-100/35">{{ $kpi['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ══ ALERTE ZONE ════════════════════════════════════════ --}}
    @php $alertZone = array_values(array_filter($zones, fn($z)=>$z['status']==='critical'))[0] ?? null; @endphp
    @if($alertZone)
    <div class="glass rounded-2xl p-4 border-red-500/30 ring-1 ring-red-500/20">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-500/15 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="alert-triangle" class="w-4.5 h-4.5 text-red-400 animate-pulse" style="width:18px;height:18px"></i>
            </div>
            <div class="flex-1">
                <p class="text-red-300 font-semibold text-sm mb-0.5">Alerte dans votre réseau</p>
                <p class="text-cyan-100/60 text-xs">
                    <strong class="text-white">{{ $alertZone['name'] }}</strong> —
                    Qualité : {{ $alertZone['quality'] }}% · Pression : {{ $alertZone['pressure'] }} bar ·
                    {{ $alertZone['incidents'] }} incident(s) actif(s)
                </p>
            </div>
            <a href="{{ route('citizen.notifications') }}"
               class="text-xs text-red-300 hover:text-white font-semibold shrink-0 transition-colors">
                Voir →
            </a>
        </div>
    </div>
    @endif

    {{-- ══ MES SIGNALEMENTS + CTA CRÉER ══════════════════════ --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Mes signalements</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">{{ count($reports) }} signalements · {{ $activeRep }} en cours</p>
            </div>
            <a href="{{ route('citizen.reports.create') }}"
               class="glass px-3 py-1.5 rounded-xl text-xs text-cyan-300 hover:text-white font-semibold
                      flex items-center gap-1.5 transition-all hover:border-cyan-400/40">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Nouveau
            </a>
        </div>

        @if(count($reports) === 0)
        <div class="flex flex-col items-center justify-center py-12 text-center px-6">
            <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 flex items-center justify-center mb-3">
                <i data-lucide="file-plus" class="w-7 h-7 text-cyan-400/50"></i>
            </div>
            <p class="text-white font-semibold text-sm mb-1">Aucun signalement</p>
            <p class="text-cyan-100/45 text-xs mb-4">Signalez un problème pour commencer</p>
            <a href="{{ route('citizen.reports.create') }}"
               class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl">
                Créer un signalement
            </a>
        </div>
        @else
        <div class="divide-y divide-white/[.04]">
            @foreach($reports as $rep)
            @php $ss = $statusStyle[$rep['status']] ?? $statusStyle['pending']; @endphp
            <a href="{{ route('citizen.reports.show', $rep['id']) }}"
               class="flex items-center gap-3 px-5 py-4 hover:bg-white/[.025] transition-colors group">
                {{-- Status dot --}}
                <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                    <span class="w-3 h-3 rounded-full {{ $ss['dot'] }}
                        {{ $rep['status']==='in_progress' ? 'animate-pulse' : '' }}"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-[10px] font-mono font-bold text-cyan-400">{{ $rep['id'] }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }}">
                            {{ $ss['label'] }}
                        </span>
                    </div>
                    <p class="text-white text-sm font-semibold truncate">{{ $rep['type'] }}</p>
                    <p class="text-cyan-100/45 text-xs truncate">{{ $rep['zone'] }} · {{ $rep['created_at'] }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/25 group-hover:text-cyan-400 transition-colors shrink-0"></i>
            </a>
            @endforeach
        </div>
        <div class="px-5 py-3 border-t border-white/5">
            <a href="{{ route('citizen.reports.create') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 bg-gradient-to-r from-cyan-500/10 to-blue-600/10
                      border border-cyan-400/20 rounded-xl text-cyan-300 text-sm font-semibold
                      hover:from-cyan-500/20 hover:to-blue-600/20 transition-all hover:border-cyan-400/40">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Signaler un nouveau problème
            </a>
        </div>
        @endif
    </div>

    {{-- ══ NOTIFICATIONS RÉCENTES ══════════════════════════════ --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Notifications</h2>
                @if($unreadNotifs > 0)
                <p class="text-cyan-100/50 text-xs mt-0.5">
                    <span class="text-amber-300 font-semibold">{{ $unreadNotifs }}</span> non lue(s)
                </p>
                @else
                <p class="text-cyan-100/50 text-xs mt-0.5">Tout est lu</p>
                @endif
            </div>
            <a href="{{ route('citizen.notifications') }}"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            @foreach(array_slice($notifs, 0, 4) as $notif)
            @php
                $nb = match($notif['color']) {
                    'amber'   => ['bg'=>'bg-amber-500/15','ic'=>'text-amber-400'],
                    'emerald' => ['bg'=>'bg-teal-500/15', 'ic'=>'text-teal-400'],
                    'blue'    => ['bg'=>'bg-blue-500/15', 'ic'=>'text-blue-400'],
                    'purple'  => ['bg'=>'bg-violet-500/15','ic'=>'text-violet-400'],
                    default   => ['bg'=>'bg-cyan-500/15', 'ic'=>'text-cyan-400'],
                };
            @endphp
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-white/[.02] transition-colors
                        {{ !$notif['read'] ? 'bg-white/[.015]' : '' }}">
                <div class="w-8 h-8 rounded-lg {{ $nb['bg'] }} flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="{{ $notif['icon'] }}" class="w-3.5 h-3.5 {{ $nb['ic'] }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        @if(!$notif['read'])
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 shrink-0"></span>
                        @endif
                        <p class="text-white text-xs font-semibold truncate">{{ $notif['title'] }}</p>
                    </div>
                    <p class="text-cyan-100/50 text-[11px] leading-tight">{{ $notif['message'] }}</p>
                </div>
                <span class="text-[10px] text-cyan-100/30 shrink-0 mt-0.5 whitespace-nowrap">{{ $notif['time'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══ DERNIÈRES FACTURES ══════════════════════════════════ --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-white font-display font-bold">Factures</h2>
            <a href="{{ route('citizen.invoices.index') }}"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            @foreach(array_slice($invoices, 0, 3) as $inv)
            @php
                $is = $inv['status'] === 'paid'
                    ? ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Payée']
                    : ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En attente'];
            @endphp
            <a href="{{ route('citizen.invoices.show', $inv['id']) }}"
               class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.025] transition-colors group">
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold">{{ $inv['month'] }}</p>
                    <p class="text-cyan-100/45 text-xs">Échéance : {{ $inv['due_date'] }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-white font-bold text-sm">{{ $inv['amount'] }} TND</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $is['bg'] }} {{ $is['text'] }}">
                        {{ $is['label'] }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ══ PROJETS D'INFRASTRUCTURE ═══════════════════════════ --}}
    <div class="glass rounded-2xl p-6 bg-gradient-to-br from-cyan-500/5 to-blue-600/5 border-cyan-400/10">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/20
                        flex items-center justify-center shrink-0">
                <i data-lucide="briefcase" class="w-6 h-6 text-cyan-300"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-white font-display font-bold text-lg mb-1">Projets d'infrastructure</h3>
                <p class="text-cyan-100/60 text-sm mb-4">
                    Consultez les projets de développement du réseau d'eau dans votre région. Découvrez les budgets, les financements et l'avancement des travaux.
                </p>
                <a href="{{ route('citizen.projets.index') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-cyan-500 to-blue-600
                          hover:from-cyan-400 hover:to-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl
                          transition-all hover-lift shadow-lg shadow-cyan-500/20 text-sm">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    Explorer les projets
                </a>
            </div>
        </div>
    </div>

    {{-- ══ CTA PRINCIPAL ══════════════════════════════════════ --}}
    <div class="glass rounded-2xl p-6 text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/20
                     flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-circle" class="w-7 h-7 text-cyan-300"></i>
        </div>
        <h3 class="text-white font-display font-bold text-lg mb-1">Vous constatez un problème ?</h3>
        <p class="text-cyan-100/55 text-sm mb-4">Signalez-le immédiatement pour une intervention rapide.</p>
        <a href="{{ route('citizen.reports.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-cyan-500 to-blue-600
                  hover:from-cyan-400 hover:to-blue-500 text-white font-semibold px-6 py-3 rounded-xl
                  transition-all hover-lift shadow-lg shadow-cyan-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Signaler un incident
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
