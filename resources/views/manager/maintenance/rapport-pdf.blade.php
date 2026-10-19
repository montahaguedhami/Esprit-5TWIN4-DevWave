<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de maintenance — {{ $mois->locale('fr')->translatedFormat('F Y') }}</title>
    <style>
        @page { margin: 26px 30px; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; font-size: 9.5px; line-height: 1.4; }
        .header { border-bottom: 2px solid #0891b2; padding-bottom: 8px; margin-bottom: 14px; }
        .brand { font-size: 17px; font-weight: bold; color: #0891b2; }
        .doc-title { font-size: 12px; color: #475569; }
        .meta { float: right; text-align: right; color: #64748b; font-size: 9px; }
        h2 { font-size: 11.5px; color: #0f172a; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; text-align: left; padding: 5px 6px; color: #475569; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .right { text-align: right; }
        .center { text-align: center; }
        .kpis td { border: 1px solid #e2e8f0; text-align: center; padding: 8px; width: 16.6%; }
        .kpi-value { font-size: 15px; font-weight: bold; color: #0f172a; }
        .kpi-label { font-size: 8.5px; color: #64748b; }
        .preventive { color: #6d28d9; font-weight: bold; }
        .footer { position: fixed; bottom: -8px; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="header">
    <div class="meta">Édité le {{ now()->format('d/m/Y à H:i') }}</div>
    <div class="brand">AquaSecure</div>
    <div class="doc-title">Rapport mensuel de maintenance — {{ ucfirst($mois->locale('fr')->translatedFormat('F Y')) }}</div>
</div>

<table class="kpis" role="presentation">
    <tr>
        <td><div class="kpi-value">{{ $interventions->count() }}</div><div class="kpi-label">Interventions</div></td>
        @foreach($par_statut as $statut => $nombre)
            <td><div class="kpi-value">{{ $nombre }}</div><div class="kpi-label">{{ $statut }}</div></td>
        @endforeach
        <td><div class="kpi-value">{{ number_format($cout_total, 2, ',', ' ') }}</div><div class="kpi-label">Coût réalisé (DT)</div></td>
    </tr>
</table>
<p>
    Répartition :
    @foreach($par_type as $type => $nombre)
        {{ $type }} {{ $nombre }}@if(! $loop->last) · @endif
    @endforeach
</p>

<h2>Par technicien</h2>
@if($par_technicien->isEmpty())
    <p>Aucune intervention ce mois-ci.</p>
@else
    <table>
        <thead><tr><th>Technicien</th><th class="center">Interventions</th><th class="center">Terminées</th><th class="right">Coût réalisé</th></tr></thead>
        <tbody>
            @foreach($par_technicien as $nom => $ligne)
                <tr>
                    <td>{{ $nom }}</td>
                    <td class="center">{{ $ligne['total'] }}</td>
                    <td class="center">{{ $ligne['terminees'] }}</td>
                    <td class="right">{{ number_format($ligne['cout'], 2, ',', ' ') }} DT</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<h2>Détail des interventions</h2>
@if($interventions->isEmpty())
    <p>Aucune intervention ce mois-ci.</p>
@else
    <table>
        <thead>
            <tr><th>#</th><th>Date</th><th>Type</th><th>Statut</th><th>Technicien</th><th>Équipement</th><th>Description</th><th class="right">Coût</th></tr>
        </thead>
        <tbody>
            @foreach($interventions as $intervention)
                <tr>
                    <td>{{ $intervention->id }}</td>
                    <td>{{ $intervention->date->format('d/m/Y') }}</td>
                    <td class="{{ $intervention->estPreventive() ? 'preventive' : '' }}">{{ $intervention->type }}</td>
                    <td>{{ $intervention->statut }}</td>
                    <td>{{ $intervention->technicien?->nom ?? '—' }}</td>
                    <td>{{ $intervention->infrastructure?->nom ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit(strtok($intervention->description, "\n"), 70) }}</td>
                    <td class="right">{{ number_format((float) $intervention->cout, 2, ',', ' ') }} DT</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="footer">AquaSecure — Rapport mensuel de maintenance généré automatiquement</div>
</body>
</html>
