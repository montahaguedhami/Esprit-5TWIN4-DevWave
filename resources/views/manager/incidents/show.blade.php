@extends('layouts.manager')

@section('manager-content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('manager.incidents') }}" class="text-cyan-300">Retour aux incidents</a>
    @if(session('success'))
        <div role="status" class="mt-4 p-3 bg-green-600 rounded">{{ session('success') }}</div>
    @endif
    <section class="py-6 border-b border-white/15">
        <h1 class="text-2xl font-bold wrap-break-word">{{ $incident->titre }}</h1>
        <p class="mt-2 text-slate-300">{{ $incident->type }} | {{ $incident->gravite }}</p>
        <p class="mt-4 whitespace-pre-line wrap-break-word">{{ $incident->description }}</p>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-sm">
            <div><dt class="text-slate-400">Citoyen</dt><dd>{{ $incident->user?->name }}</dd></div>
            <div><dt class="text-slate-400">Date de signalement</dt><dd>{{ $incident->date_signalement?->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-400">Adresse</dt><dd class="wrap-break-word">{{ $incident->localisation }}</dd></div>
        </dl>
    </section>
    @php
        $mapPoint = null;
        if (is_numeric($incident->latitude) && is_numeric($incident->longitude)) {
            $mapPoint = [$incident->latitude, $incident->longitude];
        } elseif ($incident->infrastructure && is_numeric($incident->infrastructure->latitude) && is_numeric($incident->infrastructure->longitude)) {
            $mapPoint = [$incident->infrastructure->latitude, $incident->infrastructure->longitude];
        } elseif ($incident->zone && is_numeric($incident->zone->latitude) && is_numeric($incident->zone->longitude)) {
            $mapPoint = [$incident->zone->latitude, $incident->zone->longitude];
        }
    @endphp

    <section class="py-6 border-b border-white/15">
        <h2 class="text-xl font-semibold mb-4">Carte de l’adresse</h2>
        <div id="incident-map" class="mt-4 h-96 w-full rounded-xl border border-slate-700 bg-slate-200" style="height: 420px; {{ $mapPoint ? '' : 'visibility: hidden' }}"></div>
        <p id="incident-map-status" role="status" class="mt-2 text-sm text-slate-300">Recherche de l’adresse sur la carte…</p>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mapElement = document.getElementById('incident-map');
                const statusElement = document.getElementById('incident-map-status');
                const address = @js($incident->localisation);
                const fallbackPoint = @js($mapPoint);
                const map = L.map(mapElement).setView(fallbackPoint || [36.8065, 10.1815], fallbackPoint ? 15 : 7);
                let marker = null;

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                function showAddress(point) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker(point).addTo(map);
                    const popup = document.createElement('div');
                    const title = document.createElement('strong');
                    const addressText = document.createElement('p');
                    title.textContent = @js($incident->titre);
                    addressText.textContent = address;
                    popup.append(title, addressText);
                    marker.bindPopup(popup).openPopup();
                    mapElement.style.visibility = 'visible';
                    map.setView(point, 16);
                    map.invalidateSize();
                }

                if (fallbackPoint) showAddress(fallbackPoint);

                fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(address + ', Tunisie'), {
                    headers: { 'Accept-Language': 'fr' }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Geocoding indisponible');
                        return response.json();
                    })
                    .then(results => {
                        if (!results.length) throw new Error('Adresse introuvable');
                        showAddress([Number(results[0].lat), Number(results[0].lon)]);
                        statusElement.textContent = 'Adresse localisée sur la carte.';
                    })
                    .catch(() => {
                        if (fallbackPoint) {
                            statusElement.textContent = 'Adresse non localisée précisément; la carte affiche la zone ou l’infrastructure associée.';
                        } else {
                            mapElement.style.display = 'none';
                            statusElement.textContent = 'Adresse introuvable sur la carte : ' + address;
                        }
                    });
            });
        </script>
    </section>

    <section class="py-6 border-b border-white/15">
        <h2 class="text-xl font-semibold mb-4">Actions correctives</h2>
        <a href="{{ route('manager.actions.index', $incident) }}" class="inline-flex items-center gap-2 text-cyan-300 mb-4"><i data-lucide="list" class="w-4 h-4"></i>Liste des actions</a>
        <div class="space-y-4">
            @forelse($incident->actions as $action)
                <article class="p-4 bg-white/5 rounded wrap-break-word">
                    <h3 class="font-semibold">{{ $action->titre }}</h3>
                    <p class="mt-2 whitespace-pre-line">{{ $action->description }}</p>
                    <p class="mt-2 text-sm text-slate-300">Responsable : {{ $action->responsable }} | Statut : {{ $action->statut }}</p>
                    <p class="text-sm text-slate-300">Pr&eacute;vue : {{ $action->date_prevue?->format('d/m/Y') ?? '-' }} | R&eacute;alis&eacute;e : {{ $action->date_realisation?->format('d/m/Y H:i') ?? '-' }}</p>
                    <p class="mt-2 whitespace-pre-line">{{ $action->resultat }}</p>
                    <div class="flex flex-wrap gap-4 mt-4">
                        <a href="{{ route('manager.actions.show', $action) }}" class="inline-flex items-center gap-2 text-cyan-300"><i data-lucide="eye" class="w-4 h-4"></i>Voir le d&eacute;tail</a>
                        <a href="{{ route('manager.actions.edit', $action) }}" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier l'action</a>
                        <form action="{{ route('manager.actions.destroy', $action) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cette action corrective ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer l'action</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="text-slate-300">Aucune action corrective enregistr&eacute;e.</p>
            @endforelse
        </div>
    </section>
    <section class="py-6">
        <h2 class="text-xl font-semibold mb-4">Ajouter une action corrective</h2>
        <form action="{{ route('incidents.actions.store', $incident) }}" method="post" class="space-y-4">
            @csrf
            @include('manager.actions.form', ['action' => null])
            <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter l'action</button>
        </form>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
    #incident-map { height: 420px; min-height: 320px; background: #e2e8f0; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush