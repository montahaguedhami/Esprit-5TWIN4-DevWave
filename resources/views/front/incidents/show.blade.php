@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="max-w-4xl mx-auto py-8 sm:py-10 space-y-6">
    <a href="{{ route('incidents.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-900">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>Retour aux incidents
    </a>

    @if(session('success'))
        <div role="status" class="mt-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('success') }}</div>
    @endif
    <div class="flex flex-wrap gap-4 mt-4">
        <a href="{{ route('incidents.edit', $incident) }}" class="inline-flex items-center gap-2 text-amber-700 hover:text-amber-900 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier mon incident</a>
        <form action="{{ route('incidents.destroy', $incident) }}" method="post">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-2 text-red-700 hover:text-red-900 font-semibold" onclick="return confirm('Supprimer cet incident et ses actions ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer mon incident</button>
        </form>
    </div>
    <div class="mt-4 p-5 sm:p-7 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 wrap-break-word">{{ $incident->titre }}</h1>
        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-sm text-slate-600">
            <span>{{ $incident->type }}</span><span>Gravité : {{ $incident->gravite }}</span>
        </div>
        <p class="mt-5 whitespace-pre-line wrap-break-word text-slate-700 leading-relaxed">{{ $incident->description }}</p>
        <div class="text-sm text-slate-600 mt-4">Localisation : {{ $incident->localisation }}</div>
        @php
            $incidentMapPoint = null;
            if (is_numeric($incident->latitude) && is_numeric($incident->longitude)) {
                $incidentMapPoint = [$incident->latitude, $incident->longitude];
            } elseif ($incident->infrastructure && is_numeric($incident->infrastructure->latitude) && is_numeric($incident->infrastructure->longitude)) {
                $incidentMapPoint = [$incident->infrastructure->latitude, $incident->infrastructure->longitude];
            } elseif ($incident->zone && is_numeric($incident->zone->latitude) && is_numeric($incident->zone->longitude)) {
                $incidentMapPoint = [$incident->zone->latitude, $incident->zone->longitude];
            }
        @endphp
        <section class="mt-6" aria-labelledby="incident-location-map-title">
            <h2 id="incident-location-map-title" class="text-sm font-semibold text-slate-700">Carte de la localisation</h2>
            <div id="citizen-incident-detail-map" class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100" style="height: 360px"></div>
            <p id="citizen-incident-detail-map-status" role="status" class="mt-2 text-xs text-slate-500">Recherche de la localisation sur la carte…</p>
        </section>
        @if($incident->zone)
            <div class="text-sm text-slate-600 mt-1">Zone : {{ $incident->zone->nom }}</div>
        @endif
        @if($incident->infrastructure)
            <div class="text-sm text-slate-600 mt-1">
                Équipement concerné :
                <a href="{{ route('citizen.infrastructures.show', $incident->infrastructure) }}" class="font-semibold text-cyan-700 hover:text-cyan-900">
                    {{ $incident->infrastructure->nom }}
                </a>
            </div>
        @endif
        <div class="text-xs text-slate-500 mt-1">Signalé : {{ optional($incident->date_signalement)->format('Y-m-d H:i') }}</div>

        @if($incident->preuve_path)
            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Preuve jointe</h2>
                @php $mime = mime_content_type(storage_path('app/private/' . str_replace('private/', '', $incident->preuve_path))); @endphp
                @if(str_starts_with($mime, 'image/'))
                    <img src="{{ route('incidents.attachment', $incident) }}" alt="Preuve jointe" class="mt-3 max-h-80 rounded-xl border border-slate-200 object-cover" />
                @else
                    <a href="{{ route('incidents.attachment', $incident) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        <i data-lucide="download"></i> Télécharger le document
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-slate-900">Actions correctives</h2>
        <div class="space-y-3 mt-3">
            @foreach($incident->actions as $action)
                <div class="p-4 sm:p-5 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <div class="font-semibold text-slate-900">{{ $action->titre }}</div>
                    <div class="text-sm text-slate-600 mt-1">Responsable : {{ $action->responsable }} · Statut : {{ $action->statut }}</div>
                    <div class="text-xs text-slate-500 mt-2">Prévue : {{ optional($action->date_prevue)->format('Y-m-d') }} · Réalisée : {{ optional($action->date_realisation)->format('Y-m-d H:i') }}</div>
                    <p class="mt-2 text-sm text-slate-700">{{ $action->resultat }}</p>
                </div>
            @endforeach
            @if($incident->actions->isEmpty())
                <div class="p-4 bg-white border border-slate-200 rounded-xl text-slate-500">Aucune action corrective enregistrée.</div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapElement = document.getElementById('citizen-incident-detail-map');
        const statusElement = document.getElementById('citizen-incident-detail-map-status');
        if (!mapElement || typeof L === 'undefined') return;

        const address = @js($incident->localisation);
        const fallbackPoint = @js($incidentMapPoint);
        const map = L.map(mapElement).setView(fallbackPoint || [36.8065, 10.1815], fallbackPoint ? 14 : 7);
        let marker = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        function showPoint(point) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(point).addTo(map);
            const popup = document.createElement('div');
            const title = document.createElement('strong');
            const addressText = document.createElement('p');
            title.textContent = @js($incident->titre);
            addressText.textContent = address;
            popup.append(title, addressText);
            marker.bindPopup(popup).openPopup();
            map.setView(point, 16);
            map.invalidateSize();
        }

        if (fallbackPoint) showPoint(fallbackPoint);

        const url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(address + ', Tunisie');
        fetch(url, { headers: { 'Accept-Language': 'fr' } })
            .then(response => {
                if (!response.ok) throw new Error('Recherche indisponible');
                return response.json();
            })
            .then(results => {
                if (!results.length) throw new Error('Localisation introuvable');
                showPoint([Number(results[0].lat), Number(results[0].lon)]);
                statusElement.textContent = 'Localisation affichée sur la carte.';
            })
            .catch(() => {
                statusElement.textContent = fallbackPoint
                    ? 'Adresse non localisée précisément; la carte affiche la zone ou l’infrastructure associée.'
                    : 'Localisation introuvable sur la carte.';
            });
    });
</script>
@endpush
