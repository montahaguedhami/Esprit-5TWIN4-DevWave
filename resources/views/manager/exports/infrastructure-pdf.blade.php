<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Technique Équipement - {{ $infrastructure->nom }}</title>
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
            Réf. Matériel : {{ $infrastructure->reference ?: 'INF-'.$infrastructure->id }}
        </div>
        <div class="brand">AQUASECURE</div>
        <div class="doc-title">FICHE D'INVENTAIRE & DE SUIVI DE MATÉRIEL</div>
    </div>

    {{-- Synthèse Générale --}}
    <table class="grid">
        <tr>
            <td style="width: 40%;">
                <div class="label">Désignation de l'équipement</div>
                <div class="value">{{ $infrastructure->nom }}</div>
            </td>
            <td style="width: 30%;">
                <div class="label">Typologie</div>
                <div class="value">{{ $infrastructure->type->label() }}</div>
            </td>
            <td style="width: 30%;">
                <div class="label">Zone d'appartenance</div>
                <div class="value">{{ $infrastructure->zone->nom }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">État Opérationnel</div>
                <div>
                    <span class="badge {{ $infrastructure->etat->color() === 'green' ? 'badge-green' : ($infrastructure->etat->color() === 'amber' ? 'badge-amber' : 'badge-red') }}">
                        {{ $infrastructure->etat->label() }}
                    </span>
                </div>
            </td>
            <td>
                <div class="label">Niveau de Criticité</div>
                <div>
                    <span class="badge {{ $infrastructure->niveau_criticite?->color() === 'red' ? 'badge-red' : ($infrastructure->niveau_criticite?->color() === 'amber' ? 'badge-amber' : 'badge-green') }}">
                        {{ $infrastructure->niveau_criticite?->label() ?? 'Moyen' }}
                    </span>
                </div>
            </td>
            <td>
                <div class="label">Date d'installation</div>
                <div class="value">{{ $infrastructure->date_installation->format('d/m/Y') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Fabricant / Marque</div>
                <div class="value">{{ $infrastructure->fabricant ?: 'Non renseigné' }}</div>
            </td>
            <td>
                <div class="label">Capacité nominale</div>
                <div class="value">{{ $infrastructure->capacite ? $infrastructure->capacite . ' ' . ($infrastructure->unite_capacite ?: '') : 'Non renseignée' }}</div>
            </td>
            <td>
                <div class="label">Durée de vie théorique</div>
                <div class="value">{{ $infrastructure->duree_vie_annees ? $infrastructure->duree_vie_annees . ' ans' : 'Non renseignée' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="label">Localisation précise & Coordonnées GPS</div>
                <div class="value" style="font-family: monospace;">{{ $infrastructure->latitude && $infrastructure->longitude ? $infrastructure->latitude . ' , ' . $infrastructure->longitude : 'Non géoréférencée' }} ({{ $infrastructure->localisation ?: 'Sans adresse textuelle' }})</div>
            </td>
        </tr>
    </table>

    {{-- Score de Santé Réseau --}}
    <div class="section-title">Diagnostic & Santé Multi-critères</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-lbl">Score Global Santé</div>
                <div class="kpi-val" style="color: {{ $sante['color'] === 'green' ? '#166534' : ($sante['color'] === 'amber' ? '#b45309' : '#b91c1c') }}">
                    {{ $sante['score'] }}/100
                </div>
            </td>
            <td>
                <div class="kpi-lbl">Diagnostic</div>
                <div class="kpi-val" style="font-size: 13px;">{{ $sante['label'] }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Dernière Maintenance</div>
                <div class="kpi-val" style="font-size: 12px; color: #334155;">{{ $infrastructure->date_derniere_maintenance?->format('d/m/Y') ?? 'Aucune' }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Prochaine Maintenance</div>
                <div class="kpi-val" style="font-size: 12px; color: {{ $infrastructure->date_prochaine_maintenance && $infrastructure->date_prochaine_maintenance->isPast() ? '#b91c1c' : '#166534' }};">
                    {{ $infrastructure->date_prochaine_maintenance?->format('d/m/Y') ?? 'Non planifiée' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Historique des Interventions de Maintenance --}}
    <div class="section-title">Interventions & Actions de Maintenance ({{ $infrastructure->interventions->count() }})</div>
    @if($infrastructure->interventions->isEmpty())
        <p style="color: #64748b; font-style: italic;">Aucune intervention enregistrée pour cet équipement.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Technicien</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Coût (TND)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($infrastructure->interventions as $interv)
                    <tr>
                        <td>{{ $interv->date?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $interv->technicien?->nom ?? 'Non assigné' }}</td>
                        <td>{{ $interv->description }}</td>
                        <td><span class="badge {{ $interv->statut === 'Terminée' ? 'badge-green' : 'badge-amber' }}">{{ $interv->statut }}</span></td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format((float) $interv->cout, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Historique des changements d'état --}}
    @if($infrastructure->historiques && $infrastructure->historiques->isNotEmpty())
        <div class="section-title">Historique de Traçabilité Opérationnelle</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date / Heure</th>
                    <th>Transition</th>
                    <th>Motif & Justification</th>
                    <th>Opérateur</th>
                </tr>
            </thead>
            <tbody>
                @foreach($infrastructure->historiques->take(6) as $hist)
                    <tr>
                        <td>{{ $hist->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td><strong>{{ $hist->ancien_etat?->label() ?? 'Initial' }}</strong> → <strong>{{ $hist->nouvel_etat->label() }}</strong></td>
                        <td>{{ $hist->motif }}</td>
                        <td>{{ $hist->user?->name ?? 'Système' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        AquaSecure &copy; {{ date('Y') }} — Fiche d'équipement certifiée — Page 1 / 1
    </div>
</body>
</html>
