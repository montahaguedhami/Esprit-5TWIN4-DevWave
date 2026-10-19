@extends('layouts.frontoffice')

@section('title', 'Mon Profil — AquaSecure')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════
   Profile page — light-first, dark-aware
   ═══════════════════════════════════════════════════ */

.profile-page {
    min-height: 100vh;
    background: #f3f7fb;
    padding: 1.5rem 1rem 3rem;
}
[data-theme="dark"] .profile-page { background: var(--bg-app); }

/* Card */
.pro-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 1rem;
    box-shadow: 0 2px 14px rgba(16,42,67,.06);
    overflow: hidden;
}
[data-theme="dark"] .pro-card {
    background: var(--bg-card, #1e293b);
    border-color: var(--border, #334155);
    box-shadow: 0 2px 14px rgba(0,0,0,.25);
}
.pro-card-body  { padding: 1.5rem; }
.pro-card-title {
    font-size: .9375rem;
    font-weight: 700;
    color: #152b42;
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: 1.25rem;
}
[data-theme="dark"] .pro-card-title { color: #f1f5f9; }

/* Avatar ring */
.pro-avatar-ring {
    width: 5rem; height: 5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #0891b2, #2563eb);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 800; color: #fff;
    letter-spacing: -.02em;
    margin: 0 auto;
    box-shadow: 0 4px 16px rgba(8,145,178,.3);
    position: relative;
}
.pro-avatar-edit {
    position: absolute; bottom: 0; right: 0;
    width: 1.75rem; height: 1.75rem;
    border-radius: 50%;
    background: #0891b2;
    border: 2px solid #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .15s;
}
.pro-avatar-edit:hover { background: #0e7490; }
[data-theme="dark"] .pro-avatar-edit { border-color: var(--bg-card, #1e293b); }

/* Role badge */
.pro-role-badge {
    display: inline-flex; align-items: center; gap: .375rem;
    font-size: .7rem; font-weight: 700;
    border-radius: 99px;
    padding: .25rem .75rem;
    border: 1px solid;
}
.pro-role-admin     { background: #f3e8ff; color: #6b21a8; border-color: #e9d5ff; }
.pro-role-manager   { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
.pro-role-technician{ background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
.pro-role-citizen   { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
[data-theme="dark"] .pro-role-admin     { background: rgba(139,92,246,.15); color: #c4b5fd; border-color: rgba(139,92,246,.3); }
[data-theme="dark"] .pro-role-manager   { background: rgba(59,130,246,.15); color: #93c5fd; border-color: rgba(59,130,246,.3); }
[data-theme="dark"] .pro-role-technician{ background: rgba(8,145,178,.15);  color: #67e8f9; border-color: rgba(8,145,178,.3); }
[data-theme="dark"] .pro-role-citizen   { background: rgba(16,185,129,.15); color: #6ee7b7; border-color: rgba(16,185,129,.3); }

/* Stats row */
.pro-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #dce6ef; }
[data-theme="dark"] .pro-stats { border-top-color: var(--border, #334155); }
.pro-stat-val { font-size: 1.35rem; font-weight: 800; color: #152b42; line-height: 1; }
[data-theme="dark"] .pro-stat-val { color: #f1f5f9; }
.pro-stat-val.accent-cyan    { color: #0891b2; }
.pro-stat-val.accent-emerald { color: #059669; }
.pro-stat-lbl { font-size: .65rem; color: #52677d; margin-top: .25rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
[data-theme="dark"] .pro-stat-lbl { color: #64748b; }

/* Form field */
.pro-field label { display: block; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #52677d; margin-bottom: .35rem; }
[data-theme="dark"] .pro-field label { color: #64748b; }
.pro-field input {
    width: 100%;
    background: #f8fbfd;
    border: 1px solid #cbd8e4;
    border-radius: .625rem;
    padding: .5rem .75rem;
    font-size: .875rem;
    color: #152b42;
    outline: none;
}
[data-theme="dark"] .pro-field input {
    background: var(--bg-input, #1e293b);
    border-color: var(--border, #334155);
    color: #f1f5f9;
}
.pro-field input[readonly] { cursor: default; }
.pro-field input:not([readonly]):focus { border-color: #0891b2; box-shadow: 0 0 0 3px rgba(8,145,178,.1); }

/* Setting row */
.pro-setting {
    display: flex; align-items: center; justify-content: space-between;
    padding: .875rem 1rem;
    border-radius: .75rem;
    background: #f8fbfd;
    border: 1px solid #e8eef4;
    transition: background .15s;
}
.pro-setting:hover { background: #f0f6fb; }
[data-theme="dark"] .pro-setting { background: var(--bg-muted, #0f172a); border-color: var(--border, #334155); }
[data-theme="dark"] .pro-setting:hover { background: var(--bg-hover, #1e293b); }
.pro-setting-icon {
    width: 2.25rem; height: 2.25rem; border-radius: .625rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pro-setting-title { font-size: .875rem; font-weight: 600; color: #152b42; }
[data-theme="dark"] .pro-setting-title { color: #f1f5f9; }
.pro-setting-sub { font-size: .7rem; color: #52677d; }
[data-theme="dark"] .pro-setting-sub { color: #64748b; }
.pro-setting-action { font-size: .75rem; font-weight: 600; color: #0891b2; cursor: pointer; background: none; border: none; }
.pro-setting-action:hover { color: #0e7490; text-decoration: underline; }

/* Toggle switch */
.pro-toggle { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
.pro-toggle input { position: absolute; opacity: 0; width: 0; height: 0; }
.pro-toggle-track {
    width: 2.75rem; height: 1.5rem;
    background: #cbd8e4;
    border-radius: 99px;
    transition: background .2s;
    position: relative;
}
.pro-toggle input:checked + .pro-toggle-track { background: #0891b2; }
.pro-toggle-track::after {
    content: '';
    position: absolute;
    top: 2px; left: 2px;
    width: 1.25rem; height: 1.25rem;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.15);
    transition: transform .2s;
}
.pro-toggle input:checked + .pro-toggle-track::after { transform: translateX(1.25rem); }

/* Activity item */
.pro-activity {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem;
    border-radius: .625rem;
    background: #f8fbfd;
    border: 1px solid #e8eef4;
}
[data-theme="dark"] .pro-activity { background: var(--bg-muted, #0f172a); border-color: var(--border, #334155); }
.pro-activity-icon {
    width: 2rem; height: 2rem; border-radius: .5rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.pro-activity-title { font-size: .8125rem; font-weight: 600; color: #152b42; }
[data-theme="dark"] .pro-activity-title { color: #f1f5f9; }
.pro-activity-time { font-size: .7rem; color: #52677d; }
[data-theme="dark"] .pro-activity-time { color: #64748b; }

/* Quick action buttons */
.pro-quick-btn {
    width: 100%;
    display: flex; align-items: center; gap: .625rem;
    padding: .625rem .875rem;
    border-radius: .75rem;
    border: 1px solid #dce6ef;
    background: #fff;
    font-size: .8125rem; font-weight: 600; color: #334155;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    text-align: left;
}
.pro-quick-btn:hover { background: #f0f6fb; border-color: #b6cfe0; }
[data-theme="dark"] .pro-quick-btn {
    background: var(--bg-muted, #0f172a);
    border-color: var(--border, #334155);
    color: #cbd5e1;
}
[data-theme="dark"] .pro-quick-btn:hover { background: var(--bg-hover, #1e293b); }

/* Page header */
.pro-page-title { font-size: 1.5rem; font-weight: 800; color: #152b42; }
[data-theme="dark"] .pro-page-title { color: #f1f5f9; }
.pro-page-sub { font-size: .8125rem; color: #52677d; margin-top: .25rem; }
[data-theme="dark"] .pro-page-sub { color: #64748b; }

/* Back link */
.pro-back {
    display: inline-flex; align-items: center; justify-content: center;
    width: 2.25rem; height: 2.25rem;
    border-radius: .625rem;
    background: #fff;
    border: 1px solid #dce6ef;
    color: #52677d;
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.pro-back:hover { background: #f0f6fb; color: #0891b2; }
[data-theme="dark"] .pro-back { background: var(--bg-card,#1e293b); border-color: var(--border,#334155); color: #94a3b8; }
[data-theme="dark"] .pro-back:hover { background: var(--bg-hover,#334155); color: #67e8f9; }

@media (max-width: 640px) {
    .profile-page { padding: 1rem .75rem 2rem; }
    .pro-card-body { padding: 1rem; }
}
</style>
@endpush

@section('content')
@php
    $authUser = auth()->user();
    $name     = $authUser?->name  ?? session('user.name',  'Utilisateur');
    $email    = $authUser?->email ?? session('user.email', 'user@aquasecure.tn');
    $rawRole  = $authUser?->primary_role ?? session('user.role', 'citizen');

    $roleLabels = [
        'administrateur' => 'Administrateur',
        'admin'          => 'Administrateur',
        'manager'        => 'Gestionnaire',
        'gestionnaire'   => 'Gestionnaire',
        'technicien'     => 'Technicien',
        'technician'     => 'Technicien',
        'citoyen'        => 'Citoyen',
        'citizen'        => 'Citoyen',
    ];
    $roleLabel = $roleLabels[$rawRole] ?? ucfirst($rawRole);

    $roleCss = match(true) {
        in_array($rawRole, ['administrateur','admin'])          => 'pro-role-admin',
        in_array($rawRole, ['manager','gestionnaire'])          => 'pro-role-manager',
        in_array($rawRole, ['technicien','technician'])         => 'pro-role-technician',
        default                                                  => 'pro-role-citizen',
    };

    // Initials
    $initials = collect(explode(' ', $name))
        ->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))
        ->take(2)->join('');
@endphp

<div class="profile-page">
    <div class="mx-auto max-w-5xl space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex items-center gap-3 mb-2">
            <a href="javascript:history.back()" class="pro-back" aria-label="Retour">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            </a>
            <div>
                <h1 class="pro-page-title">Mon Profil</h1>
                <p class="pro-page-sub">Gérez vos informations et préférences</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── LEFT: Avatar + quick actions ── --}}
            <div class="space-y-4">

                {{-- Avatar card --}}
                <div class="pro-card">
                    <div class="pro-card-body text-center">
                        <div class="pro-avatar-ring">
                            {{ $initials }}
                            <div class="pro-avatar-edit" title="Modifier la photo">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            </div>
                        </div>

                        <h2 style="font-size:1.1rem;font-weight:700;color:#152b42;margin:.875rem 0 .2rem" class="[data-theme=dark]:text-slate-100">
                            {{ $name }}
                        </h2>
                        <p style="font-size:.8125rem;color:#52677d;margin-bottom:.75rem">{{ $email }}</p>

                        <span class="pro-role-badge {{ $roleCss }}">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            {{ $roleLabel }}
                        </span>

                        <div class="pro-stats">
                            <div class="text-center">
                                <div class="pro-stat-val">42</div>
                                <div class="pro-stat-lbl">Actions</div>
                            </div>
                            <div class="text-center">
                                <div class="pro-stat-val accent-cyan">15</div>
                                <div class="pro-stat-lbl">En cours</div>
                            </div>
                            <div class="text-center">
                                <div class="pro-stat-val accent-emerald">27</div>
                                <div class="pro-stat-lbl">Terminés</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick actions --}}
                <div class="pro-card">
                    <div class="pro-card-body">
                        <h3 class="pro-card-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            Actions rapides
                        </h3>
                        <div class="space-y-2">
                            <button class="pro-quick-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Changer le mot de passe
                            </button>
                            <button class="pro-quick-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                Préférences notifications
                            </button>
                            <button class="pro-quick-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                Sécurité et confidentialité
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── RIGHT: Info + settings + activity ── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Personal information --}}
                <div class="pro-card">
                    <div class="pro-card-body">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="pro-card-title" style="margin-bottom:0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Informations personnelles
                            </h3>
                            <button style="display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;font-weight:600;color:#0891b2;background:#e0f2fe;border:1px solid #bae6fd;border-radius:.5rem;padding:.35rem .75rem;cursor:pointer;transition:background .15s"
                                    onmouseover="this.style.background='#bae6fd'" onmouseout="this.style.background='#e0f2fe'">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Modifier
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="pro-field">
                                <label>Nom complet</label>
                                <input type="text" value="{{ $name }}" readonly>
                            </div>
                            <div class="pro-field">
                                <label>Adresse email</label>
                                <input type="email" value="{{ $email }}" readonly>
                            </div>
                            <div class="pro-field">
                                <label>Téléphone</label>
                                <input type="text" value="+216 98 765 432" readonly>
                            </div>
                            <div class="pro-field">
                                <label>Rôle</label>
                                <input type="text" value="{{ $roleLabel }}" readonly>
                            </div>
                            <div class="pro-field sm:col-span-2">
                                <label>Adresse</label>
                                <input type="text" value="42 Avenue Habib Bourguiba, Tunis 1000" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account settings --}}
                <div class="pro-card">
                    <div class="pro-card-body">
                        <h3 class="pro-card-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 17.66l-1.41 1.41M19.07 19.07l-1.41-1.41M5.34 6.34L3.93 4.93M21 12h-2M5 12H3M12 21v-2M12 5V3"/></svg>
                            Paramètres du compte
                        </h3>
                        <div class="space-y-3">

                            <div class="pro-setting">
                                <div style="display:flex;align-items:center;gap:.75rem">
                                    <div class="pro-setting-icon" style="background:#e0f2fe">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    </div>
                                    <div>
                                        <div class="pro-setting-title">Langue</div>
                                        <div class="pro-setting-sub">Français</div>
                                    </div>
                                </div>
                                <button class="pro-setting-action">Changer</button>
                            </div>

                            <div class="pro-setting">
                                <div style="display:flex;align-items:center;gap:.75rem">
                                    <div class="pro-setting-icon" style="background:#dbeafe">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <div>
                                        <div class="pro-setting-title">Fuseau horaire</div>
                                        <div class="pro-setting-sub">Africa/Tunis (GMT+1)</div>
                                    </div>
                                </div>
                                <button class="pro-setting-action">Changer</button>
                            </div>

                            <div class="pro-setting">
                                <div style="display:flex;align-items:center;gap:.75rem">
                                    <div class="pro-setting-icon" style="background:#f3e8ff">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    </div>
                                    <div>
                                        <div class="pro-setting-title">Notifications par email</div>
                                        <div class="pro-setting-sub">Recevoir les alertes importantes</div>
                                    </div>
                                </div>
                                <label class="pro-toggle">
                                    <input type="checkbox" checked>
                                    <span class="pro-toggle-track"></span>
                                </label>
                            </div>

                            <div class="pro-setting">
                                <div style="display:flex;align-items:center;gap:.75rem">
                                    <div class="pro-setting-icon" style="background:#dcfce7">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                                    </div>
                                    <div>
                                        <div class="pro-setting-title">Authentification à deux facteurs</div>
                                        <div class="pro-setting-sub">Sécurité renforcée</div>
                                    </div>
                                </div>
                                <button class="pro-setting-action">Activer</button>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Activity log --}}
                <div class="pro-card">
                    <div class="pro-card-body">
                        <h3 class="pro-card-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            Activité récente
                        </h3>
                        <div class="space-y-2">
                            @foreach([
                                ['action' => 'Connexion réussie',          'time' => 'Il y a 2h',      'bg' => '#dcfce7', 'stroke' => '#059669',
                                 'icon' => 'M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3'],
                                ['action' => 'Modification du profil',     'time' => 'Il y a 1 jour',  'bg' => '#dbeafe', 'stroke' => '#2563eb',
                                 'icon' => 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'],
                                ['action' => 'Changement de mot de passe', 'time' => 'Il y a 3 jours', 'bg' => '#f3e8ff', 'stroke' => '#7c3aed',
                                 'icon' => 'M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4'],
                                ['action' => 'Nouvelle déclaration créée', 'time' => 'Il y a 5 jours', 'bg' => '#e0f2fe', 'stroke' => '#0891b2',
                                 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M12 18v-6M9 15h6'],
                            ] as $act)
                            <div class="pro-activity">
                                <div class="pro-activity-icon" style="background: {{ $act['bg'] }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $act['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $act['icon'] }}"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="pro-activity-title">{{ $act['action'] }}</div>
                                    <div class="pro-activity-time">{{ $act['time'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
