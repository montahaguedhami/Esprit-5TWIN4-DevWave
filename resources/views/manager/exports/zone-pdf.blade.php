<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Technique Zone - {{ $zone->nom }}</title>
    <style>
        @page { margin: 25px 30px; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0891b2;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .brand {
            font-size: 20px;
            font-weight: bold;
            color: #0891b2;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 14px;
            color: #475569;
            margin-top: 2px;
        }
        .meta-date {
            float: right;
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .grid td {
            vertical-align: top;
            padding: 4px 6px;
        }
        .label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .kpi-table td {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
            width: 25%;
        }
        .kpi-val {
            font-size: 16px;
            font-weight: bold;
            color: #0891b2;
            margin-top: 2px;
        }
        .kpi-lbl {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
            color: #334155;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-amber { background-color: #fef3c7; color: #92400e; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-cyan { background-color: #cffafe; color: #155e75; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="meta-date">
            Généré le : {{ $generatedAt }}<br>
            Référence doc : AQ-ZON-{{ $zone->id }}
        </div>
        <div class="brand">AQUASECURE</div>
        <div class="doc-title">FICHE D'INVENTAIRE ET D'AUDIT TECHNIQUE DE ZONE</div>
    </div>

    {{-- Synthèse Générale --}}
    <table class="grid" role="presentation">
        <tr>
            <td style="width: 50%;">
                <div class="label">Zone d'exploitation</div>
                <div class="value">{{ $zone->nom }}</div>
            </td>
            <td style="width: 25%;">
                <div class="label">Statut</div>
                <div><span class="badge {{ $zone->statut?->value === 'active' ? 'badge-green' : 'badge-amber' }}">{{ $zone->statut?->label() ?? 'Active' }}</span></div>
            </td>
            <td style="width: 25%;">
                <div class="label">Responsable désigné</div>
                <div class="value">{{ $zone->responsable?->name ?? 'Non assigné' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Localisation administrative</div>
                <div class="value">{{ $zone->localisation }}</div>
            </td>
            <td>
                <div class="label">Superficie couverte</div>
                <div class="value">{{ $zone->superficie_km2 ? number_format((float)$zone->superficie_km2, 2, ',', ' ') . ' km²' : 'Non renseignée' }}</div>
            </td>
            <td>
                <div class="label">Population desservie</div>
                <div class="value">{{ $zone->population_desservie ? number_format((int)$zone->population_desservie, 0, ',', ' ') . ' hab.' : 'Non renseignée' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="label">Coordonnées GPS centrales</div>
                <div class="value" style="font-family: monospace;">{{ $zone->latitude && $zone->longitude ? $zone->latitude . ' , ' . $zone->longitude : 'Non géoréférencée' }}</div>
            </td>
        </tr>
    </table>

    {{-- Indicateurs Opérationnels --}}
    <div class="section-title">Indicateurs Opérationnels Clés (KPI)</div>
    <table class="kpi-table" role="presentation">
        <tr>
            <td>
                <div class="kpi-lbl">Total Équipements</div>
                <div class="kpi-val">{{ $stats['total'] }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Taux en Service</div>
                <div class="kpi-val" style="color: {{ $stats['pourcentage_en_service'] >= 80 ? '#166534' : '#b45309' }}">{{ $stats['pourcentage_en_service'] }}%</div>
            </td>
            <td>
                <div class="kpi-lbl">Score Santé Moyen</div>
                <div class="kpi-val">{{ $stats['score_sante_moyen'] }}/100</div>
            </td>
            <td>
                <div class="kpi-lbl">Retards Maintenance</div>
                <div class="kpi-val" style="color: {{ $stats['nombre_en_retard_maintenance'] > 0 ? '#b91c1c' : '#166534' }}">{{ $stats['nombre_en_retard_maintenance'] }}</div>
            </td>
        </tr>
    </table>

    {{-- Liste des infrastructures rattachées --}}
    <div class="section-title">Parc des Équipements & Ouvrages ({{ $zone->infrastructures->count() }})</div>
    @if($zone->infrastructures->isEmpty())
        <p style="color: #64748b; font-style: italic;">Aucun équipement enregistré dans cette zone.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom & Réf.</th>
                    <th>Type</th>
                    <th>État</th>
                    <th>Criticité</th>
                    <th>Santé</th>
                    <th>Dernière Maint.</th>
                    <th>Prochaine Maint.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zone->infrastructures as $infra)
                    @php $sante = $infra->sante; @endphp
                    <tr>
                        <td>
                            <strong>{{ $infra->nom }}</strong>
                            @if($infra->reference)<br><span style="font-size: 8px; color: #64748b; font-family: monospace;">{{ $infra->reference }}</span>@endif
                        </td>
                        <td>{{ $infra->type->label() }}</td>
                        <td>
                            <span class="badge {{ $infra->etat->color() === 'green' ? 'badge-green' : ($infra->etat->color() === 'amber' ? 'badge-amber' : 'badge-red') }}">
                                {{ $infra->etat->label() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $infra->niveau_criticite?->color() === 'red' ? 'badge-red' : ($infra->niveau_criticite?->color() === 'amber' ? 'badge-amber' : 'badge-green') }}">
                                {{ $infra->niveau_criticite?->label() ?? 'Moyen' }}
                            </span>
                        </td>
                        <td><strong>{{ $sante['score'] }}/100</strong> ({{ $sante['label'] }})</td>
                        <td>{{ $infra->date_derniere_maintenance?->format('d/m/Y') ?? '—' }}</td>
                        <td style="{{ $infra->date_prochaine_maintenance && $infra->date_prochaine_maintenance->isPast() ? 'color:#b91c1c; font-weight:bold;' : '' }}">
                            {{ $infra->date_prochaine_maintenance?->format('d/m/Y') ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Incidents récents --}}
    @if($zone->incidents && $zone->incidents->isNotEmpty())
        <div class="section-title">Incidents Récents Enregistrés</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Titre</th>
                    <th>Gravité</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zone->incidents->take(5) as $incident)
                    <tr>
                        <td>{{ $incident->date_signalement?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $incident->titre }}</td>
                        <td><span class="badge {{ $incident->gravite === 'critique' ? 'badge-red' : 'badge-amber' }}">{{ $incident->gravite }}</span></td>
                        <td>{{ $incident->statut }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        AquaSecure &copy; {{ date('Y') }} — Document officiel d'exploitation du réseau d'eau potable — Page 1 / 1
    </div>
</body>
</html>
