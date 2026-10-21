@extends('layouts.manager')

@section('title', 'Planning des interventions — AquaSecure')

@push('styles')
<style>
    /* FullCalendar aux couleurs du thème sombre */
    #planning-calendar {
        --fc-border-color: rgba(255, 255, 255, .08);
        --fc-page-bg-color: transparent;
        --fc-neutral-bg-color: rgba(255, 255, 255, .03);
        --fc-today-bg-color: rgba(34, 211, 238, .08);
        --fc-button-bg-color: rgba(255, 255, 255, .06);
        --fc-button-border-color: rgba(255, 255, 255, .12);
        --fc-button-hover-bg-color: rgba(34, 211, 238, .18);
        --fc-button-hover-border-color: rgba(34, 211, 238, .35);
        --fc-button-active-bg-color: rgba(34, 211, 238, .28);
        --fc-button-active-border-color: rgba(34, 211, 238, .45);
        --fc-event-text-color: #fff;
        color: rgba(207, 250, 254, .85);
        font-size: .85rem;
    }
    #planning-calendar .fc-toolbar-title { color: #fff; font-size: 1.15rem; font-weight: 600; text-transform: capitalize; }
    #planning-calendar .fc-col-header-cell-cushion,
    #planning-calendar .fc-daygrid-day-number { color: rgba(207, 250, 254, .7); text-decoration: none; }
    #planning-calendar .fc-daygrid-day.fc-day-past { opacity: .75; }
    #planning-calendar .fc-event { cursor: pointer; padding: 1px 4px; border-width: 1px; }
    #planning-calendar .fc-event.fc-conflit { border-width: 2px; box-shadow: 0 0 0 1px #f43f5e; }
    #planning-calendar .fc-daygrid-day:hover { background: rgba(34, 211, 238, .04); }
</style>
@endpush

@section('manager-content')
<div class="max-w-6xl mx-auto">
    <x-maintenance.page-header title="Planning des interventions" subtitle="Glissez une intervention pour la replanifier, cliquez sur un jour pour en créer une" icon="calendar-days">
        <x-maintenance.link-button :href="route('manager.interventions.index')" variant="secondary" icon="clipboard-list">Liste</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.interventions.create')" icon="plus">Nouvelle intervention</x-maintenance.link-button>
    </x-maintenance.page-header>

    @include('manager.maintenance._flash')

    {{-- Maintenance préventive automatique --}}
    <x-ui.card padding="sm" class="mb-6 border border-violet-500/20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-display font-semibold text-white flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-5 h-5 text-violet-300"></i> Maintenance préventive
                </h2>
                <p class="text-xs text-cyan-100/60 mt-1">
                    Planifie les équipements dont la maintenance arrive à échéance sous {{ config('maintenance.horizon_jours') }} jours,
                    avec le technicien disponible le plus adapté. Exécutée automatiquement chaque jour à 6h15.
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                <form method="POST" action="{{ route('manager.planning.preventive') }}">
                    @csrf
                    <input type="hidden" name="simulation" value="1">
                    <x-ui.button type="submit" variant="secondary" icon="eye">Simuler</x-ui.button>
                </form>
                <form method="POST" action="{{ route('manager.planning.preventive') }}"
                      onsubmit="return confirm('Créer les interventions préventives à échéance ?');">
                    @csrf
                    <x-ui.button type="submit" icon="calendar-plus">Planifier</x-ui.button>
                </form>
            </div>
        </div>

        @if($rapport = session('rapport_preventif'))
            @if(! empty($rapport['lignes']))
                <div class="overflow-x-auto mt-4 pt-4 border-t border-white/10">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left">
                                <th class="px-3 py-2 text-xs font-semibold text-cyan-100/50">Infrastructure</th>
                                <th class="px-3 py-2 text-xs font-semibold text-cyan-100/50">Échéance</th>
                                <th class="px-3 py-2 text-xs font-semibold text-cyan-100/50">Résultat</th>
                                <th class="px-3 py-2 text-xs font-semibold text-cyan-100/50">Date</th>
                                <th class="px-3 py-2 text-xs font-semibold text-cyan-100/50">Technicien</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($rapport['lignes'] as $ligne)
                                <tr>
                                    <td class="px-3 py-2 text-white">{{ $ligne['infrastructure'] }}</td>
                                    <td class="px-3 py-2 text-cyan-100/80">{{ $ligne['echeance'] }}</td>
                                    <td class="px-3 py-2">
                                        @switch($ligne['resultat'])
                                            @case(\App\Services\PreventiveMaintenanceService::PLANIFIEE)
                                                @if($ligne['intervention_id'])
                                                    <a href="{{ route('manager.interventions.show', $ligne['intervention_id']) }}" class="text-emerald-300 hover:underline">Planifiée #{{ $ligne['intervention_id'] }}</a>
                                                @else
                                                    <span class="text-violet-300">À planifier</span>
                                                @endif
                                                @break
                                            @case(\App\Services\PreventiveMaintenanceService::DEJA_PLANIFIEE)
                                                <span class="text-cyan-100/60">Déjà planifiée</span>
                                                @break
                                            @default
                                                <span class="text-rose-300">Aucun technicien disponible</span>
                                        @endswitch
                                    </td>
                                    <td class="px-3 py-2 text-cyan-100/80">{{ $ligne['date'] ?? '—' }}</td>
                                    <td class="px-3 py-2 text-cyan-100/80">
                                        {{ $ligne['technicien'] ?? '—' }}
                                        @if($ligne['hors_specialite'])<span class="text-amber-300 text-xs">(hors spécialité)</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </x-ui.card>

    {{-- Message de retour du glisser-déposer --}}
    <div id="planning-feedback" class="hidden mb-4 rounded-xl px-4 py-3 text-sm flex items-center gap-2" role="status"></div>

    @if($conflits->isNotEmpty())
        <x-ui.card padding="sm" class="mb-6 border border-rose-500/30">
            <h2 class="font-display font-semibold text-rose-300 flex items-center gap-2 mb-3">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                {{ $conflits->count() }} conflit(s) de planification à venir
            </h2>
            <ul class="space-y-2 text-sm">
                @foreach($conflits as $conflit)
                    <li class="text-cyan-100/80">
                        <span class="font-semibold text-white">{{ $conflit['technicien']->nom }}</span>
                        — {{ $conflit['date']->translatedFormat('l d F Y') }} :
                        @foreach($conflit['interventions'] as $intervention)
                            <a href="{{ route('manager.interventions.show', $intervention) }}" class="text-cyan-300 hover:underline">#{{ $intervention->id }}</a>@if(! $loop->last), @endif
                        @endforeach
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if($enConge->isNotEmpty())
        <x-ui.card padding="sm" class="mb-6 border border-amber-500/30">
            <h2 class="font-display font-semibold text-amber-300 flex items-center gap-2 mb-3">
                <i data-lucide="user-x" class="w-5 h-5"></i>
                {{ $enConge->count() }} intervention(s) à venir affectée(s) à un technicien en congé — à réaffecter
            </h2>
            <ul class="space-y-2 text-sm">
                @foreach($enConge as $intervention)
                    <li class="text-cyan-100/80">
                        <a href="{{ route('manager.interventions.edit', $intervention) }}" class="text-cyan-300 hover:underline">#{{ $intervention->id }}</a>
                        — {{ $intervention->date->format('d/m/Y') }} ·
                        <span class="font-semibold text-white">{{ $intervention->technicien->nom }}</span> ·
                        {{ Str::limit($intervention->description, 50) }}
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    <x-ui.card padding="sm" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4 justify-between">
            <x-forms.select name="technicien_id" label="Technicien" placeholder="Tous les techniciens" class="sm:max-w-xs">
                @foreach($techniciens as $technicien)
                    <option value="{{ $technicien->id }}">{{ $technicien->nom }}{{ $technicien->disponibilite === 'En congé' ? ' (en congé)' : '' }}</option>
                @endforeach
            </x-forms.select>

            <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-cyan-100/70">
                @foreach($couleurs as $statut => $couleur)
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded" style="background: {{ $couleur }}"></span>{{ $statut }}</span>
                @endforeach
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded border-2 border-rose-500"></span>Conflit</span>
                <span class="flex items-center gap-1.5"><span class="text-white">⟳</span>Préventive</span>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="sm">
        <div id="planning-calendar"></div>
    </x-ui.card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js" integrity="sha384-B1OFx8Gy9GjPu8UbUyXbGQpzll9ubAUQ9agInFJ8NnD7nYG1u/CLR+Sqr5yifl4q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/fr.global.min.js" integrity="sha384-JAvKXRZVkNs+jHQB9thGYPx3J0NTR+B8/bjh00vSzKTuOs7TZUN3ioWKYalj2Yhb" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var filtre = document.getElementById('technicien_id');
    var feedback = document.getElementById('planning-feedback');
    var createUrl = @json(route('manager.interventions.create'));
    var moveUrl = @json(route('manager.planning.deplacer', ['intervention' => '__ID__']));

    function afficher(message, succes) {
        feedback.textContent = message;
        feedback.className = 'mb-4 rounded-xl px-4 py-3 text-sm flex items-center gap-2 ' +
            (succes ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30'
                    : 'bg-rose-500/10 text-rose-300 border border-rose-500/30');
        clearTimeout(afficher.timer);
        afficher.timer = setTimeout(function () { feedback.classList.add('hidden'); }, 6000);
    }

    var calendar = new FullCalendar.Calendar(document.getElementById('planning-calendar'), {
        locale: 'fr',
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        dayMaxEvents: 3,
        editable: true,
        eventDurationEditable: false,
        events: {
            url: @json(route('manager.planning.evenements')),
            extraParams: function () { return filtre.value ? { technicien_id: filtre.value } : {}; },
            failure: function () { afficher('Impossible de charger les interventions.', false); }
        },
        eventDidMount: function (info) {
            var p = info.event.extendedProps;
            info.el.title = [p.type, p.statut, p.technicien, p.infrastructure, p.conflit ? '⚠ Conflit de planification' : null]
                .filter(Boolean).join(' · ');
        },
        dateClick: function (info) {
            var params = new URLSearchParams({ date: info.dateStr });
            if (filtre.value) { params.set('technicien_id', filtre.value); }
            window.location = createUrl + '?' + params.toString();
        },
        eventDrop: function (info) {
            fetch(moveUrl.replace('__ID__', info.event.id), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ date: info.event.startStr })
            })
            .then(function (response) {
                return response.json().then(function (data) { return { ok: response.ok, data: data }; });
            })
            .then(function (result) {
                var message = result.data.message || (result.data.errors && Object.values(result.data.errors)[0][0]) || 'Erreur inattendue.';
                if (! result.ok) { info.revert(); }
                afficher(message, result.ok);
                // Recharge pour mettre à jour les marqueurs de conflit
                calendar.refetchEvents();
            })
            .catch(function () {
                info.revert();
                afficher('Erreur réseau : la replanification a été annulée.', false);
            });
        }
    });

    calendar.render();
    filtre.addEventListener('change', function () { calendar.refetchEvents(); });
});
</script>
@endpush
