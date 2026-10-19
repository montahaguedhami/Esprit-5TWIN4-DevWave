<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport financier et budgétaire — AquaSecure</title>
    <style>
        @page { margin: 14mm 14mm 16mm 14mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.4;
        }
        .header { border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 20px; font-weight: bold; color: #0891b2; letter-spacing: 0.5px; }
        .doc-title { font-size: 13px; color: #475569; margin-top: 2px; }
        .meta-date { float: right; text-align: right; font-size: 9px; color: #64748b; }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin: 14px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.kpi-table td {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 7px 6px;
            text-align: center;
            width: 16.66%;
        }
        .kpi-val { font-size: 14px; font-weight: bold; color: #0891b2; margin-top: 2px; }
        .kpi-val.green { color: #059669; }
        .kpi-val.teal  { color: #0d9488; }
        .kpi-lbl { font-size: 8px; color: #64748b; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8px;
            text-transform: uppercase;
            color: #334155;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 9px;
        }
        table.data-table td.num { text-align: right; font-family: monospace; white-space: nowrap; }
        table.data-table th.num { text-align: right; }
        table.data-table tr.total td {
            background-color: #ecfdf5;
            font-weight: bold;
            border-top: 2px solid #10b981;
        }
        table.data-table tr.alt td { background-color: #fafafa; }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green  { background-color: #dcfce7; color: #166534; }
        .badge-amber  { background-color: #fef3c7; color: #92400e; }
        .badge-red    { background-color: #fee2e2; color: #991b1b; }
        .badge-cyan   { background-color: #cffafe; color: #155e75; }
        .badge-slate  { background-color: #e2e8f0; color: #334155; }

        .note {
            font-size: 8px;
            color: #64748b;
            font-style: italic;
            margin-top: 4px;
        }
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
        .avoid-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    <div class="header">
        <div class="meta-date">
            Généré le : {{ $generatedAt }}<br>
            Document : AQ-BUD-{{ now()->format('Ymd') }}<br>
            Émetteur : {{ $emetteur }}
        </div>
        <div class="brand">AQUASECURE</div>
        <div class="doc-title">RAPPORT FINANCIER ET BUDGÉTAIRE — FINANCEMENT ET DÉPENSES DES TRAVAUX MUNICIPAUX</div>
        <div class="note">Données recalculées depuis la base de l'application à l'instant de la génération.</div>
    </div>

    {{-- ── 1. Indicateurs clés ── --}}
    <div class="section-title">Indicateurs clés</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-lbl">Budget total des projets</div>
                <div class="kpi-val">{{ number_format((float) $stats['totalBudget'], 0, ',', ' ') }} DT</div>
            </td>
            <td>
                <div class="kpi-lbl">Total financé</div>
                <div class="kpi-val green">{{ number_format((float) $stats['totalFinance'], 0, ',', ' ') }} DT</div>
            </td>
            <td>
                <div class="kpi-lbl">Budget restant</div>
                <div class="kpi-val teal">{{ number_format((float) $stats['budgetRestant'], 0, ',', ' ') }} DT</div>
            </td>
            <td>
                <div class="kpi-lbl">Taux de financement</div>
                <div class="kpi-val">{{ number_format((float) $stats['pourcentageUtilise'], 1) }} %</div>
            </td>
            <td>
                <div class="kpi-lbl">Projets suivis</div>
                <div class="kpi-val">{{ $stats['projets']->count() }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Sources de financement</div>
                <div class="kpi-val">{{ $stats['sourcesUniques'] }}<span style="font-size:9px;"> ({{ $stats['financements']->count() }} opérations)</span></div>
            </td>
        </tr>
    </table>

    {{-- ── 2. Situation par projet ── --}}
    <div class="section-title avoid-break">Situation budgétaire par projet ({{ $parProjet->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:32%;">Projet</th>
                <th style="width:11%;">Statut</th>
                <th class="num" style="width:14%;">Budget (DT)</th>
                <th class="num" style="width:14%;">Financé (DT)</th>
                <th class="num" style="width:14%;">Restant (DT)</th>
                <th class="num" style="width:8%;">% Fin.</th>
                <th class="num" style="width:7%;">Ops.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parProjet as $ligne)
            @php
                $statutBadge = match ($ligne['statut']) {
                    'planifie'  => 'badge-cyan',
                    'en_cours'  => 'badge-amber',
                    'termine'   => 'badge-green',
                    'suspendu'  => 'badge-slate',
                    'annule'    => 'badge-red',
                    default     => 'badge-slate',
                };
                $statutLabel = match ($ligne['statut']) {
                    'planifie'  => 'Planifié',
                    'en_cours'  => 'En cours',
                    'termine'   => 'Terminé',
                    'suspendu'  => 'Suspendu',
                    'annule'    => 'Annulé',
                    default     => $ligne['statut'],
                };
            @endphp
            <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                <td>{{ $ligne['nom'] }}</td>
                <td><span class="badge {{ $statutBadge }}">{{ $statutLabel }}</span></td>
                <td class="num">{{ number_format($ligne['budget'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['finance'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['restant'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($ligne['pourcentage'], 1) }} %</td>
                <td class="num">{{ $ligne['nbFinancements'] }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">TOTAL GÉNÉRAL</td>
                <td class="num">{{ number_format((float) $stats['totalBudget'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format((float) $stats['totalFinance'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format((float) $stats['budgetRestant'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format((float) $stats['pourcentageUtilise'], 1) }} %</td>
                <td class="num">{{ $stats['financements']->count() }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── 3. Répartition par source ── --}}
    <div class="section-title avoid-break">Répartition par source de financement ({{ $parSource->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:50%;">Source</th>
                <th class="num" style="width:15%;">Nb opérations</th>
                <th class="num" style="width:20%;">Montant (DT)</th>
                <th class="num" style="width:15%;">% du total financé</th>
            </tr>
        </thead>
        <tbody>
            @php $totalFinance = (float) $stats['totalFinance']; @endphp
            @foreach($parSource as $ligne)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                <td>{{ $ligne['source'] }}</td>
                <td class="num">{{ $ligne['nombre'] }}</td>
                <td class="num">{{ number_format($ligne['montant'], 2, ',', ' ') }}</td>
                <td class="num">{{ number_format($totalFinance > 0 ? $ligne['montant'] / $totalFinance * 100 : 0, 1) }} %</td>
            </tr>
            @endforeach
            <tr class="total">
                <td>TOTAL</td>
                <td class="num">{{ $stats['financements']->count() }}</td>
                <td class="num">{{ number_format($totalFinance, 2, ',', ' ') }}</td>
                <td class="num">100,0 %</td>
            </tr>
        </tbody>
    </table>

    {{-- ── 4. Détail des financements ── --}}
    <div class="section-title avoid-break">Détail des opérations de financement ({{ $stats['financements']->count() }})</div>
    @if($stats['financements']->isEmpty())
        <p style="color:#64748b; font-style:italic;">Aucun financement enregistré.</p>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:10%;">Date</th>
                <th style="width:28%;">Projet</th>
                <th style="width:26%;">Source</th>
                <th class="num" style="width:13%;">Montant (DT)</th>
                <th class="num" style="width:13%;">Budget projet (DT)</th>
                <th class="num" style="width:10%;">% Fin.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['financements'] as $financement)
                @php $projet = $financement->projet; @endphp
                <tr class="{{ $loop->iteration % 2 === 0 ? 'alt' : '' }}">
                    <td>{{ $financement->date_financement?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $projet?->nom ?? 'Projet supprimé' }}</td>
                    <td>{{ $financement->source }}</td>
                    <td class="num">{{ number_format((float) $financement->montant, 2, ',', ' ') }}</td>
                    <td class="num">{{ $projet ? number_format((float) $projet->budget, 2, ',', ' ') : '—' }}</td>
                    <td class="num">{{ $projet ? number_format((float) $projet->pourcentage_finance, 1) . ' %' : '—' }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3">TOTAL</td>
                <td class="num">{{ number_format($totalFinance, 2, ',', ' ') }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        AquaSecure &copy; {{ date('Y') }} — Rapport financier généré localement à {{ $generatedAt }} — Page <page-number></page-number>
    </div>
</body>
</html>
