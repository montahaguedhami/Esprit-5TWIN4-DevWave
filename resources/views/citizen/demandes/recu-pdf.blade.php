<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de contribution {{ $paiement->reference_interne }} — AquaSecure</title>
    <style>
        @page { margin: 20mm 18mm; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
        }

        .header { border-bottom: 2px solid #14b8a6; padding-bottom: 10px; margin-bottom: 14px; }
        .brand  { font-size: 22px; font-weight: bold; color: #0891b2; letter-spacing: 0.5px; }
        .doc-title { font-size: 13px; color: #475569; margin-top: 2px; }
        .meta   { float: right; text-align: right; font-size: 10px; color: #64748b; }
        .ref    { font-family: monospace; font-size: 12px; font-weight: bold; color: #0891b2; }

        /* ── Mention obligatoire ── */
        .alert {
            background-color: #fffbeb;
            border: 1px solid #f59e0b;
            border-left: 5px solid #f59e0b;
            padding: 10px 12px;
            margin-bottom: 16px;
        }
        .alert-title {
            font-size: 12px;
            font-weight: bold;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .alert-text { font-size: 10px; color: #92400e; margin-top: 4px; }

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

        table.fields { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.fields td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table.fields td.label { color: #64748b; width: 42%; font-size: 10px; text-transform: uppercase; }
        table.fields td.value { color: #0f172a; font-weight: bold; text-align: right; }

        .project-name { font-size: 13px; font-weight: bold; color: #0f172a; }
        .project-desc { font-size: 10px; color: #475569; margin-top: 3px; }

        .amount-box {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 4px 0;
        }
        .amount-box td {
            background-color: #ecfdf5;
            border: 1px solid #99f6e4;
            padding: 12px;
        }
        .amount-label { font-size: 9px; color: #0f766e; text-transform: uppercase; font-weight: bold; }
        .amount-value { font-size: 24px; font-weight: bold; color: #0f766e; text-align: right; }
        .amount-cur   { font-size: 12px; color: #5eead4; }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background-color: #ccfbf1; color: #115e59; }
        .badge-red   { background-color: #fee2e2; color: #991b1b; }

        .note {
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 16px;
            line-height: 1.5;
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
    </style>
</head>
<body>

    <div class="header">
        <div class="meta">
            Référence : <span class="ref">{{ $paiement->reference_interne }}</span><br>
            Émis le : {{ ($paiement->confirmed_at ?? $paiement->created_at)->format('d/m/Y à H:i') }}<br>
            Généré le : {{ $generatedAt }}
        </div>
        <div class="brand">AQUASECURE</div>
        <div class="doc-title">REÇU DE CONTRIBUTION SIMULÉE</div>
    </div>

    {{-- ── Mention obligatoire, identique à celle affichée à l'écran ── --}}
    <div class="alert">
        <div class="alert-title">Simulation — aucune transaction financière réelle</div>
        <div class="alert-text">
            Ce document est une démonstration académique. Il ne constitue ni une preuve de paiement,
            ni un justificatif comptable, ni une confirmation bancaire. Aucun argent n'a été débité
            ou transféré et aucune passerelle de paiement externe n'a été contactée.
        </div>
    </div>

    {{-- ── Données du bénéficiaire ── --}}
    <div class="section-title">Bénéficiaire de la contribution</div>
    <table class="fields">
        <tr>
            <td class="label">Nom</td>
            <td class="value">{{ $paiement->citoyen?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">E-mail</td>
            <td class="value">{{ $paiement->citoyen?->email ?? '—' }}</td>
        </tr>
    </table>

    {{-- ── Projet financé ── --}}
    <div class="section-title">Projet financé</div>
    <div class="project-name">{{ $paiement->demande?->projet?->nom ?? '—' }}</div>
    @if($paiement->demande?->projet?->description)
    <div class="project-desc">{{ \Illuminate\Support\Str::limit($paiement->demande->projet->description, 320) }}</div>
    @endif

    {{-- ── Montant ── --}}
    <table class="amount-box">
        <tr>
            <td>
                <div class="amount-label">Montant de la contribution</div>
            </td>
            <td class="amount-value">
                {{ number_format((float) $paiement->montant, 2, ',', ' ') }}
                <span class="amount-cur">{{ $paiement->devise }}</span>
            </td>
        </tr>
    </table>

    {{-- ── Détail de l'opération ── --}}
    <div class="section-title">Détail de l'opération</div>
    <table class="fields">
        <tr>
            <td class="label">Statut</td>
            <td class="value">
                <span class="badge {{ $paiement->estConfirme() ? 'badge-green' : 'badge-red' }}">
                    {{ $paiement->libelleStatut() }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Demande liée</td>
            <td class="value">n° {{ $paiement->demande_id }}</td>
        </tr>
        <tr>
            <td class="label">Référence interne</td>
            <td class="value" style="font-family: monospace;">{{ $paiement->reference_interne }}</td>
        </tr>
        <tr>
            <td class="label">Demandée le</td>
            <td class="value">{{ $paiement->demande?->created_at?->format('d/m/Y à H:i') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Décidée par</td>
            <td class="value">{{ $paiement->demande?->decideur?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Traitée le</td>
            <td class="value">{{ ($paiement->confirmed_at ?? $paiement->created_at)->format('d/m/Y à H:i') }}</td>
        </tr>
        @if($paiement->motif)
        <tr>
            <td class="label">Observation</td>
            <td class="value" style="font-weight: normal;">{{ $paiement->motif }}</td>
        </tr>
        @endif
    </table>

    <div class="note">
        Document généré automatiquement par AquaSecure le {{ $generatedAt }}.
        Ce reçu atteste uniquement de l'enregistrement d'une <strong>contribution simulée</strong>
        dans le cadre d'un projet académique. Il ne vaut pas preuve de réception de fonds.
    </div>

    <div class="footer">
        AquaSecure &copy; {{ date('Y') }} — Reçu de contribution simulée — Référence {{ $paiement->reference_interne }}
    </div>
</body>
</html>
