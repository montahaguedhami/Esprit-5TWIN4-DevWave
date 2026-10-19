@extends('layouts.auth')

@section('auth-content')
<div class="auth-login">
    <div class="auth-login__glow auth-login__glow--top" aria-hidden="true"></div>
    <div class="auth-login__glow auth-login__glow--bottom" aria-hidden="true"></div>

    <div class="auth-login__card">
        <section class="auth-login__intro">
            <div class="auth-login__eyebrow">
                <span class="auth-login__status" aria-hidden="true"></span>
                Plateforme nationale de surveillance
            </div>

            <div>
                <p class="auth-login__brand">AquaSecure</p>
                <h1>Un réseau mieux suivi,<br><span>une eau plus sûre.</span></h1>
                <p class="auth-login__description">
                    Retrouvez votre espace et suivez les informations de votre réseau en toute simplicité.
                </p>
            </div>

            <div class="auth-login__stats" aria-label="À propos de la plateforme">
                <div>
                    <strong>24/7</strong>
                    <span>suivi continu</span>
                </div>
                <div>
                    <strong>14</strong>
                    <span>zones suivies</span>
                </div>
                <div>
                    <strong>100%</strong>
                    <span>transparence</span>
                </div>
            </div>
        </section>

        <section class="auth-login__form-panel">
            <div class="auth-login__heading">
                <div>
                    <p class="auth-login__kicker">Accès sécurisé</p>
                    <h2>Heureux de vous revoir</h2>
                    <p>Connectez-vous à votre compte AquaSecure.</p>
                </div>
                <span class="auth-login__heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M10 17l5-5-5-5M15 12H3m9-8h5a4 4 0 014 4v8a4 4 0 01-4 4h-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>

            <nav class="auth-login__tabs" aria-label="Options de compte">
                <a href="{{ route('auth.login') }}" aria-current="page" class="is-active">Connexion</a>
                <a href="{{ route('auth.register') }}">Inscription</a>
                <a href="{{ route('auth.forgot-password') }}">Mot de passe</a>
            </nav>

            <form method="POST" action="{{ route('login.post') }}" class="auth-login__form">
                @csrf

                @if ($errors->any())
                    <div role="alert" class="auth-login__error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="auth-login__field">
                    <label for="email">Adresse email</label>
                    <div class="auth-login__input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 6.75A1.75 1.75 0 014.75 5h14.5A1.75 1.75 0 0121 6.75v10.5A1.75 1.75 0 0119.25 19H4.75A1.75 1.75 0 013 17.25V6.75z" stroke="currentColor" stroke-width="1.7"/>
                            <path d="m4 6 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="admin@aquasecure.tn" autocomplete="username">
                    </div>
                </div>

                <div class="auth-login__field">
                    <label for="password">Mot de passe</label>
                    <div class="auth-login__input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.7"/>
                            <path d="M8 10V7a4 4 0 118 0v3m-4 5v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        <input id="password" type="password" name="password" required placeholder="Votre mot de passe" autocomplete="current-password">
                        <button type="button" class="auth-login__password-toggle" onclick="togglePasswordVisibility()" aria-label="Afficher le mot de passe">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6z" stroke="currentColor" stroke-width="1.7"/>
                                <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
                                <path d="M3 3l18 18M10.6 6.2A9.8 9.8 0 0112 6c6.1 0 9.5 6 9.5 6a16 16 0 01-3.1 3.6M6.2 6.7C3.8 8.3 2.5 12 2.5 12s3.4 6 9.5 6c1 0 1.9-.2 2.7-.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.9 9.9a3 3 0 004.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <a href="{{ route('auth.forgot-password') }}" class="auth-login__forgot">Mot de passe oublié ?</a>

                <button type="submit" class="auth-login__submit">
                    Se connecter
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
        </section>
    </div>
</div>

@push('styles')
<style>
    .auth-login {
        --login-navy: #0c2a4a;
        --login-cyan: #06aeca;
        position: relative;
        isolation: isolate;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 100vh;
        overflow: hidden;
        padding: 7rem 1.5rem 3rem;
        color: #18334b;
        background:
            radial-gradient(ellipse at 12% 12%, rgba(207, 246, 250, .85), transparent 34rem),
            radial-gradient(ellipse at 92% 86%, rgba(222, 239, 255, .8), transparent 32rem),
            #f7fcfe;
        font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
    }

    .auth-login__glow {
        position: absolute;
        z-index: -1;
        width: 24rem;
        height: 24rem;
        border-radius: 50%;
        background: rgba(14, 165, 198, .08);
        filter: blur(2px);
        pointer-events: none;
    }

    .auth-login__glow--top { top: -15rem; right: 9%; }
    .auth-login__glow--bottom { bottom: -18rem; left: 12%; width: 30rem; height: 30rem; }

    .auth-login__card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, .92fr);
        width: min(100%, 68rem);
        min-height: 39rem;
        overflow: hidden;
        border: 1px solid rgba(12, 42, 74, .08);
        border-radius: 1.75rem;
        background: #fff;
        box-shadow: 0 30px 90px rgba(12, 42, 74, .12), 0 4px 16px rgba(12, 42, 74, .04);
    }

    .auth-login__intro {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(2.25rem, 5vw, 4rem);
        color: #fff;
        background:
            radial-gradient(ellipse at 0 100%, rgba(40, 202, 216, .26), transparent 50%),
            linear-gradient(145deg, #0c2a4a 4%, #10436a 60%, #087f9c 125%);
    }

    .auth-login__eyebrow {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        gap: .65rem;
        padding: .65rem .9rem;
        border: 1px solid rgba(189, 244, 249, .24);
        border-radius: 999px;
        color: #d8f7fa;
        background: rgba(255, 255, 255, .08);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .025em;
    }

    .auth-login__status {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: #52e0c0;
        box-shadow: 0 0 0 4px rgba(82, 224, 192, .14);
    }

    .auth-login__brand {
        margin: 0 0 .75rem;
        color: #75e2ed;
        font-size: .95rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .auth-login__intro h1 {
        margin: 0;
        color: #fff;
        font-family: 'Inter Tight', 'Inter', sans-serif;
        font-size: clamp(2.35rem, 4vw, 3.65rem);
        font-weight: 800;
        letter-spacing: -.045em;
        line-height: 1.08;
    }

    .auth-login__intro h1 span { color: #77e1e9; }

    .auth-login__description {
        max-width: 27rem;
        margin: 1.4rem 0 0;
        color: rgba(235, 250, 253, .78);
        font-size: 1rem;
        line-height: 1.8;
    }

    .auth-login__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-top: 2.5rem;
    }

    .auth-login__stats div {
        display: flex;
        flex-direction: column;
        gap: .3rem;
        padding: 1rem .85rem;
        border: 1px solid rgba(255, 255, 255, .13);
        border-radius: 1rem;
        background: rgba(255, 255, 255, .08);
    }

    .auth-login__stats strong { color: #fff; font-size: 1.35rem; font-weight: 800; }
    .auth-login__stats span { color: rgba(230, 249, 251, .7); font-size: .7rem; font-weight: 600; }

    .auth-login__form-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: clamp(2rem, 4.5vw, 3.75rem);
        background: #fff;
    }

    .auth-login__heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .auth-login__kicker {
        margin: 0 0 .4rem;
        color: #0799b7;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .auth-login__heading h2 {
        margin: 0;
        color: var(--login-navy);
        font-family: 'Inter Tight', 'Inter', sans-serif;
        font-size: clamp(1.6rem, 2.5vw, 2rem);
        font-weight: 800;
        letter-spacing: -.035em;
        line-height: 1.2;
    }

    .auth-login__heading p:last-child {
        margin: .55rem 0 0;
        color: #708397;
        font-size: .86rem;
    }

    .auth-login__heading-icon {
        display: grid;
        width: 3.1rem;
        height: 3.1rem;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid #d6f1f5;
        border-radius: 1rem;
        color: #078eaa;
        background: #effbfd;
    }

    .auth-login__heading-icon svg { width: 1.45rem; height: 1.45rem; }

    .auth-login__tabs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .25rem;
        margin: 1.75rem 0 1.5rem;
        padding: .3rem;
        border-radius: .9rem;
        background: #f1f6f8;
    }

    .auth-login__tabs a {
        padding: .65rem .3rem;
        border-radius: .65rem;
        color: #718497;
        font-size: .76rem;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        transition: color .2s ease, background .2s ease, box-shadow .2s ease;
    }

    .auth-login__tabs a:hover { color: #087f9c; }
    .auth-login__tabs a.is-active { color: #087f9c; background: #fff; box-shadow: 0 2px 8px rgba(12, 42, 74, .08); }

    .auth-login__form { display: flex; flex-direction: column; gap: 1.15rem; }
    .auth-login__field label { display: block; margin-bottom: .5rem; color: #28445b; font-size: .8rem; font-weight: 700; }

    .auth-login__input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .auth-login__input-wrap > svg {
        position: absolute;
        left: 1rem;
        width: 1.15rem;
        height: 1.15rem;
        color: #91a5b5;
        pointer-events: none;
    }

    .auth-login__input-wrap input {
        width: 100%;
        min-height: 3.15rem;
        padding: .8rem 3rem .8rem 2.9rem !important;
        border: 1px solid #dce7ec;
        border-radius: .85rem;
        outline: none;
        color: #18334b;
        background: #fbfdfe;
        font-size: .88rem;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .auth-login__input-wrap input::placeholder { color: #a4b3bf; }
    .auth-login__input-wrap input:focus { border-color: #45bfd0; background: #fff; box-shadow: 0 0 0 4px rgba(6, 174, 202, .11); }

    .auth-login__input-wrap input:-webkit-autofill,
    .auth-login__input-wrap input:-webkit-autofill:hover,
    .auth-login__input-wrap input:-webkit-autofill:focus {
        padding-left: 2.9rem !important;
        -webkit-text-fill-color: #18334b;
        -webkit-box-shadow: 0 0 0 1000px #fbfdfe inset;
    }

    .auth-login__password-toggle {
        position: absolute;
        right: .85rem;
        display: grid;
        width: 1.6rem;
        height: 1.6rem;
        place-items: center;
        border: 0;
        color: #8397a8;
        background: transparent;
        cursor: pointer;
    }

    .auth-login__password-toggle:hover { color: #078eaa; }
    .auth-login__password-toggle svg { width: 1.15rem; height: 1.15rem; }

    .auth-login__forgot {
        align-self: flex-end;
        margin-top: -.45rem;
        color: #078eaa;
        font-size: .78rem;
        font-weight: 700;
        text-decoration: none;
    }

    .auth-login__forgot:hover { color: #0c2a4a; text-decoration: underline; }

    .auth-login__submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .7rem;
        min-height: 3.2rem;
        margin-top: .25rem;
        border: 0;
        border-radius: .85rem;
        color: #fff;
        background: linear-gradient(105deg, #08aeca, #1472b8);
        box-shadow: 0 10px 22px rgba(8, 150, 190, .2);
        font-size: .9rem;
        font-weight: 800;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .auth-login__submit:hover { transform: translateY(-1px); box-shadow: 0 13px 26px rgba(8, 150, 190, .28); }
    .auth-login__submit svg { width: 1.1rem; height: 1.1rem; }

    .auth-login__error {
        padding: .8rem 1rem;
        border: 1px solid #fecaca;
        border-radius: .75rem;
        color: #b42318;
        background: #fff5f4;
        font-size: .82rem;
    }

    @media (max-width: 767px) {
        .auth-login { min-height: 100vh; padding: 6rem 1rem 2rem; }
        .auth-login__card { display: block; width: min(100%, 31rem); min-height: auto; border-radius: 1.3rem; }
        .auth-login__intro { display: none; }
        .auth-login__form-panel { padding: 1.75rem 1.3rem; }
        .auth-login__tabs { margin-top: 1.4rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-login *,
        .auth-login *::before,
        .auth-login *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const eyeIcon = document.querySelector('.eye-icon');
        const eyeOffIcon = document.querySelector('.eye-off-icon');
        const toggle = document.querySelector('.auth-login__password-toggle');

        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.hidden = true;
            eyeOffIcon.hidden = false;
            toggle.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
            input.type = 'password';
            eyeIcon.hidden = false;
            eyeOffIcon.hidden = true;
            toggle.setAttribute('aria-label', 'Afficher le mot de passe');
        }
    }
</script>
@endpush
@endsection
