@extends('layouts.frontoffice')

@section('title', 'Modifier mon profil — AquaSecure')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('profile.show') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:.625rem;background:var(--bg-card,#fff);border:1px solid var(--border,#dce6ef);color:var(--text-secondary,#475569);transition:background .15s;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;color:#152b42;">Modifier mon profil</h1>
            <p style="font-size:.8125rem;color:var(--text-secondary,#475569);margin-top:.25rem;">Mettez à jour vos informations personnelles et paramètres de sécurité</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="margin-bottom:1.5rem;padding:1rem;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:.75rem;">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="margin-bottom:1.5rem;padding:1rem;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:.75rem;">
        ❌ {{ session('error') }}
    </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- ── Photo de profil ── --}}
        <div style="background:#fff;border:1px solid #dce6ef;border-radius:1rem;box-shadow:0 2px 14px rgba(16,42,67,.06);overflow:hidden;">
            <div style="padding:1.5rem;">
                <h2 style="font-size:1.125rem;font-weight:700;color:#152b42;margin-bottom:1.25rem;">Photo de profil</h2>

                <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:1.5rem;">
                    <div style="width:5rem;height:5rem;border-radius:50%;background:linear-gradient(135deg,#0891b2,#2563eb);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;color:#fff;flex-shrink:0;overflow:hidden;">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->join('') }}
                        @endif
                    </div>
                    <div>
                        <p style="font-size:.875rem;font-weight:600;color:#152b42;">{{ $user->name }}</p>
                        <p style="font-size:.8125rem;color:#475569;">{{ $user->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" style="margin-bottom:1rem;">
                    @csrf
                    @method('PATCH')
                    <div style="margin-bottom:1rem;">
                        <label for="avatar" style="display:block;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.5rem;">
                            Nouvelle photo (JPEG, PNG, WebP — max 2 Mo)
                        </label>
                        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" required
                               style="width:100%;padding:.5rem .75rem;border:1px solid #cbd8e4;border-radius:.625rem;font-size:.875rem;background:#f8fbfd;color:#152b42;">
                        @error('avatar')
                        <p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.25rem;background:#2563eb;color:#fff;border:none;border-radius:.625rem;font-size:.875rem;font-weight:600;cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Uploader la photo
                    </button>
                </form>

                @if($user->avatar)
                <form method="POST" action="{{ route('profile.avatar.delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Supprimer votre photo de profil ?')"
                            style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.25rem;background:#dc2626;color:#fff;border:none;border-radius:.625rem;font-size:.875rem;font-weight:600;cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Supprimer la photo
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- ── Informations personnelles ── --}}
        <div style="background:#fff;border:1px solid #dce6ef;border-radius:1rem;box-shadow:0 2px 14px rgba(16,42,67,.06);overflow:hidden;">
            <div style="padding:1.5rem;">
                <h2 style="font-size:1.125rem;font-weight:700;color:#152b42;margin-bottom:1.25rem;">Informations personnelles</h2>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin-bottom:1.5rem;">
                        <div>
                            <label for="name" style="display:block;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.35rem;">
                                Nom complet <span style="color:#dc2626;">*</span>
                            </label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   style="width:100%;background:#f8fbfd;border:1px solid #cbd8e4;border-radius:.625rem;padding:.5rem .75rem;font-size:.875rem;color:#152b42;box-sizing:border-box;">
                            @error('name')
                            <p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" style="display:block;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.35rem;">
                                Adresse email <span style="color:#dc2626;">*</span>
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   style="width:100%;background:#f8fbfd;border:1px solid #cbd8e4;border-radius:.625rem;padding:.5rem .75rem;font-size:.875rem;color:#152b42;box-sizing:border-box;">
                            @error('email')
                            <p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button type="submit"
                            style="display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.5rem;background:linear-gradient(135deg,#0891b2,#2563eb);color:#fff;border:none;border-radius:.75rem;font-size:.875rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(8,145,178,.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                        Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Changer le mot de passe ── --}}
        <div style="background:#fff;border:1px solid #dce6ef;border-radius:1rem;box-shadow:0 2px 14px rgba(16,42,67,.06);overflow:hidden;">
            <div style="padding:1.5rem;">
                <h2 style="font-size:1.125rem;font-weight:700;color:#152b42;margin-bottom:1.25rem;">Changer le mot de passe</h2>

                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PATCH')

                    <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;">
                        <div>
                            <label for="current-password" style="display:block;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.35rem;">
                                Mot de passe actuel <span style="color:#dc2626;">*</span>
                            </label>
                            <input id="current-password" type="password" name="current_password" required
                                   style="width:100%;background:#f8fbfd;border:1px solid #cbd8e4;border-radius:.625rem;padding:.5rem .75rem;font-size:.875rem;color:#152b42;box-sizing:border-box;">
                            @error('current_password')
                            <p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" style="display:block;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.35rem;">
                                Nouveau mot de passe <span style="color:#dc2626;">*</span>
                            </label>
                            <input id="password" type="password" name="password" required minlength="8"
                                   style="width:100%;background:#f8fbfd;border:1px solid #cbd8e4;border-radius:.625rem;padding:.5rem .75rem;font-size:.875rem;color:#152b42;box-sizing:border-box;">
                            <p style="font-size:.75rem;color:#475569;margin-top:.35rem;">Min. 8 caractères, avec majuscule, minuscule, chiffre et symbole.</p>
                            @error('password')
                            <p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password-confirmation" style="display:block;font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#334155;margin-bottom:.35rem;">
                                Confirmer le nouveau mot de passe <span style="color:#dc2626;">*</span>
                            </label>
                            <input id="password-confirmation" type="password" name="password_confirmation" required
                                   style="width:100%;background:#f8fbfd;border:1px solid #cbd8e4;border-radius:.625rem;padding:.5rem .75rem;font-size:.875rem;color:#152b42;box-sizing:border-box;">
                        </div>
                    </div>

                    <button type="submit"
                            style="display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.5rem;background:linear-gradient(135deg,#0891b2,#2563eb);color:#fff;border:none;border-radius:.75rem;font-size:.875rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(8,145,178,.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>

        {{-- ── 2FA ── --}}
        <div style="background:#fff;border:1px solid #dce6ef;border-radius:1rem;box-shadow:0 2px 14px rgba(16,42,67,.06);overflow:hidden;">
            <div style="padding:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h2 style="font-size:1.125rem;font-weight:700;color:#152b42;margin-bottom:.5rem;">Authentification à deux facteurs</h2>
                    <p style="font-size:.875rem;color:#475569;">
                        @if($user->two_factor_confirmed_at)
                            ✅ Activé — Votre compte est protégé par le 2FA
                        @else
                            ⚠️ Non activé — Renforcez la sécurité de votre compte
                        @endif
                    </p>
                </div>
                <a href="{{ route('profile.two-factor') }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.25rem;background:#0891b2;color:#fff;border-radius:.625rem;font-size:.875rem;font-weight:600;text-decoration:none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Gérer le 2FA
                </a>
            </div>
        </div>

        {{-- ── Zone de danger ── --}}
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:1rem;overflow:hidden;">
            <div style="padding:1.5rem;">
                <h2 style="font-size:1.125rem;font-weight:700;color:#991b1b;margin-bottom:.75rem;">⚠️ Zone de danger</h2>
                <p style="font-size:.875rem;color:#b91c1c;margin-bottom:1.25rem;">
                    Cette action est <strong>irréversible</strong>. Toutes vos données seront définitivement supprimées.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div style="margin-bottom:1rem;">
                        <label for="password-2" style="display:block;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#991b1b;margin-bottom:.35rem;">
                            Confirmez votre mot de passe <span style="color:#dc2626;">*</span>
                        </label>
                        <input id="password-2" type="password" name="password" required
                               style="width:100%;background:#fff;border:1px solid #fca5a5;border-radius:.625rem;padding:.5rem .75rem;font-size:.875rem;color:#152b42;box-sizing:border-box;max-width:320px;">
                        @error('password')
                        <p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            onclick="return confirm('ATTENTION : Cette action est IRRÉVERSIBLE. Confirmer la suppression de votre compte ?')"
                            style="display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.5rem;background:#dc2626;color:#fff;border:none;border-radius:.75rem;font-size:.875rem;font-weight:700;cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                        Supprimer mon compte définitivement
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
