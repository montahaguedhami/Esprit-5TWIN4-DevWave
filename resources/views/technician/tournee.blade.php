@extends('layouts.manager')

@section('title', 'Tournée du Jour — Technicien')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #tournee-map {
        height: 480px;
        width: 100%;
        border-radius: 1rem;
        z-index: 10;
    }
    .tournee-marker-icon {
        background: #087e9b;
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 50%;
        font-weight: bold;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(8, 126, 155, 0.5);
    }
    .tournee-marker-icon.active {
        background: #f59e0b;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.6);
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('manager-content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- En-tête mobile --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 text-xs font-semibold mb-2">
                <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                Itinéraire Terrain · {{ now()->translatedFormat('l d F Y') }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Tournée de Maintenance</h1>
            <p class="text-cyan-100/60 text-sm mt-0.5">Ordre d'intervention et guidage géographique pour vos missions du jour.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('technician.interventions.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span>Liste des interventions</span>
            </a>
            <a href="{{ route('technician.scan') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md transition-all">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span>Scanner Ouvrage</span>
            </a>
        </div>
    </div>

    {{-- KPI Tournée --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="glass rounded-2xl p-4 border border-white/10">
            <span class="text-xs text-cyan-100/60 block mb-1">Missions prévues</span>
            <span class="text-2xl font-bold font-display text-white">{{ $stats['total'] }}</span>
        </div>
        <div class="glass rounded-2xl p-4 border border-amber-500/20">
            <span class="text-xs text-amber-300 block mb-1">En cours</span>
            <span class="text-2xl font-bold font-display text-white">{{ $stats['en_cours'] }}</span>
        </div>
        <div class="glass rounded-2xl p-4 border border-cyan-500/20">
            <span class="text-xs text-cyan-300 block mb-1">Planifiées</span>
            <span class="text-2xl font-bold font-display text-white">{{ $stats['planifiees'] }}</span>
        </div>
        <div class="glass rounded-2xl p-4 border border-emerald-500/20">
            <span class="text-xs text-emerald-300 block mb-1">Terminées</span>
            <span class="text-2xl font-bold font-display text-white">{{ $stats['terminees'] }}</span>
        </div>
    </div>

    {{-- Carte Leaflet de la tournée --}}
    <div class="glass rounded-3xl p-4 border border-white/10 shadow-xl space-y-3">
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Carte Interactive de la Tournée</span>
            </div>
            <span class="text-[11px] text-cyan-100/50">{{ count($mapPoints) }} point(s) géolocalisé(s)</span>
        </div>

        <div id="tournee-map"></div>
    </div>

    {{-- Feuille de route / Étape par étape --}}
    <div class="glass rounded-3xl p-5 border border-white/10 space-y-4">
        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="check-square" class="w-4 h-4 text-cyan-400"></i>
            Feuille de Route Détaillée ({{ $interventions->count() }} à traiter)
        </h2>

        @if($interventions->isEmpty())
            <div class="p-8 text-center text-xs text-cyan-100/50">
                <p>Aucune intervention programmée pour aujourd'hui. Toutes vos missions sont clôturées.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($interventions as $idx => $item)
                @php
                    $infra = $item->infrastructure;
                    $isFirst = ($idx === 0);
                @endphp
                <div class="p-4 rounded-2xl bg-slate-900/60 border {{ $item->statut === 'En cours' ? 'border-amber-500/40 bg-amber-500/5' : 'border-white/10' }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-full {{ $item->statut === 'En cours' ? 'bg-amber-500 text-slate-950 font-black' : 'bg-cyan-500/20 text-cyan-300 font-bold' }} flex items-center justify-center shrink-0 text-xs">
                            {{ $idx + 1 }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $item->statut === 'En cours' ? 'bg-amber-500/20 text-amber-300' : 'bg-cyan-500/20 text-cyan-300' }}">
                                    {{ $item->statut }}
                                </span>
                                <span class="text-xs text-cyan-100/60">{{ $item->date?->format('d/m/Y') }}</span>
                                <span class="text-xs font-mono text-cyan-400">#{{ $item->id }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-white">
                                {{ $infra?->nom ?? 'Ouvrage non rattaché' }}
                            </h3>
                            <p class="text-xs text-cyan-100/60 mt-0.5">
                                {{ $infra?->localisation ?? 'Localisation non renseignée' }}
                            </p>
                            <p class="text-xs text-white/90 mt-2 bg-slate-950/40 p-2.5 rounded-xl border border-white/5">
                                {{ $item->description }}
                            </p>
                        </div>
                    </div>

                    {{-- Actions rapides sur l'étape --}}
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                        @if($infra)
                        <a href="{{ route('technician.infrastructures.show', $infra) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                            Fiche Ouvrage
                        </a>
                        @endif
                        <a href="{{ route('technician.interventions.report', $item) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white transition-all">
                            Rapport
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const points = @json($mapPoints);
    const map = L.map('tournee-map').setView([36.8065, 10.1815], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const latlngs = [];

    points.forEach(pt => {
        const isActive = (pt.statut === 'En cours');
        const customIcon = L.divIcon({
            className: 'tournee-marker-icon ' + (isActive ? 'active' : ''),
            html: `<span>${pt.order}</span>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        const marker = L.marker([pt.lat, pt.lng], { icon: customIcon }).addTo(map);
        latlngs.push([pt.lat, pt.lng]);

        const popupContent = `
            <div style="font-family: inherit; font-size: 12px; color: #1e293b; min-width: 180px;">
                <div style="font-weight: bold; margin-bottom: 2px;">#${pt.order} — ${pt.infra_nom}</div>
                <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">${pt.zone} · ${pt.statut}</div>
                <div style="display: flex; gap: 4px; margin-top: 6px;">
                    ${pt.url_fiche ? `<a href="${pt.url_fiche}" style="color: #087e9b; font-weight: bold; text-decoration: underline;">Voir Fiche</a> · ` : ''}
                    <a href="${pt.url_rapport}" style="color: #087e9b; font-weight: bold; text-decoration: underline;">Faire Rapport</a>
                </div>
            </div>
        `;
        marker.bindPopup(popupContent);
    });

    if (latlngs.length > 1) {
        // Tracer la polyligne d'itinéraire entre les étapes
        L.polyline(latlngs, {
            color: '#087e9b',
            weight: 3,
            dashArray: '6, 8',
            opacity: 0.8
        }).addTo(map);
        map.fitBounds(L.latLngBounds(latlngs), { padding: [40, 40] });
    } else if (latlngs.length === 1) {
        map.setView(latlngs[0], 13);
    }
});
</script>
@endpush
