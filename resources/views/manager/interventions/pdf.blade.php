<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'intervention #{{ $intervention->id }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; font-size: 10.5px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0891b2; padding-bottom: 10px; margin-bottom: 16px; }
        .brand { font-size: 18px; font-weight: bold; color: #0891b2; }
        .doc-title { font-size: 13px; color: #475569; margin-top: 2px; }
        .meta { float: right; text-align: right; color: #64748b; font-size: 9.5px; }
        h2 { font-size: 12px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 4px 6px; vertical-align: top; }
        .info td.label { width: 30%; color: #64748b; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 8px; font-size: 9.5px; font-weight: bold; }
        .b-planifiee { background: #e0f2fe; color: #0369a1; }
        .b-en-cours { background: #fef3c7; color: #b45309; }
        .b-terminee { background: #d1fae5; color: #047857; }
        .b-annulee { background: #f1f5f9; color: #475569; }
        .b-preventive { background: #ede9fe; color: #6d28d9; }
        .b-corrective { background: #e0f2fe; color: #0369a1; }
        .description { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; white-space: pre-line; }
        .histo th { background: #f1f5f9; text-align: left; padding: 5px 6px; font-size: 9.5px; color: #475569; }
        .histo td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; }
        .right { text-align: right; }
        .signatures td { width: 50%; padding: 8px; }
        .sign-box { border: 1px dashed #94a3b8; height: 60px; border-radius: 6px; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #94a3b8; }
    </style>
</head>
<body>
@php
    $classeStatut = 'b-'.\Illuminate\Support\Str::slug($intervention->statut);
    $infra = $intervention->infrastructure;
@endphp

<div class="header">
    <div class="meta">
        Rapport n° INT-{{ str_pad($intervention->id, 5, '0', STR_PAD_LEFT) }}<br>
        Édité le {{ now()->format('d/m/Y à H:i') }}
    </div>
    <div class="brand">AquaSecure</div>
    <div class="doc-title">Rapport d'intervention de maintenance</div>
</div>

<h2>Intervention</h2>
<table class="info">
    <tr><td class="label">Date</td><td>{{ $intervention->date->locale('fr')->translatedFormat('l d F Y') }}</td></tr>
    <tr>
        <td class="label">Type / statut</td>
        <td>
            <span class="badge {{ $intervention->estPreventive() ? 'b-preventive' : 'b-corrective' }}">{{ $intervention->type }}</span>
            <span class="badge {{ $classeStatut }}">{{ $intervention->statut }}</span>
        </td>
    </tr>
    <tr><td class="label">Coût</td><td>{{ number_format((float) $intervention->cout, 2, ',', ' ') }} DT</td></tr>
</table>

<h2>Technicien</h2>
<table class="info">
    <tr><td class="label">Nom</td><td>{{ $intervention->technicien?->nom ?? '—' }}</td></tr>
    <tr><td class="label">Spécialité</td><td>{{ $intervention->technicien?->specialite ?? '—' }}</td></tr>
    <tr><td class="label">Téléphone</td><td>{{ $intervention->technicien?->telephone ?? '—' }}</td></tr>
</table>

<h2>Équipement</h2>
@if($infra)
    <table class="info">
        <tr><td class="label">Nom / référence</td><td>{{ $infra->nom }}{{ $infra->reference ? ' ('.$infra->reference.')' : '' }}</td></tr>
        <tr><td class="label">Type</td><td>{{ $infra->type?->value ?? '—' }}</td></tr>
        <tr><td class="label">Zone</td><td>{{ $infra->zone?->nom ?? '—' }}{{ $infra->localisation ? ' — '.$infra->localisation : '' }}</td></tr>
        <tr><td class="label">Criticité</td><td>{{ $infra->niveau_criticite?->value ? ucfirst($infra->niveau_criticite->value) : '—' }}</td></tr>
        <tr>
            <td class="label">Maintenance</td>
            <td>
                dernière : {{ $infra->date_derniere_maintenance?->format('d/m/Y') ?? '—' }}
                · prochaine : {{ $infra->date_prochaine_maintenance?->format('d/m/Y') ?? '—' }}
            </td>
        </tr>
    </table>
@else
    <p>Intervention non rattachée à un équipement.</p>
@endif

<h2>Description et rapport terrain</h2>
<div class="description">{{ $intervention->description }}</div>

@if($historique->isNotEmpty())
    <h2>Historique récent de l'équipement</h2>
    <table class="histo">
        <thead>
            <tr><th>Date</th><th>Type</th><th>Statut</th><th>Technicien</th><th class="right">Coût</th></tr>
        </thead>
        <tbody>
            @foreach($historique as $precedente)
                <tr>
                    <td>{{ $precedente->date->format('d/m/Y') }}</td>
                    <td>{{ $precedente->type }}</td>
                    <td>{{ $precedente->statut }}</td>
                    <td>{{ $precedente->technicien?->nom ?? '—' }}</td>
                    <td class="right">{{ number_format((float) $precedente->cout, 2, ',', ' ') }} DT</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<h2>Validation</h2>
<table class="signatures">
    <tr>
        <td>Le technicien<div class="sign-box"></div></td>
        <td>Le gestionnaire<div class="sign-box"></div></td>
    </tr>
</table>

<div class="footer">AquaSecure — Rapport généré automatiquement · Intervention #{{ $intervention->id }}</div>
</body>
</html>
