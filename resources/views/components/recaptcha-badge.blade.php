@props(['action' => 'default'])

@php
    /** @var \App\Services\RecaptchaVerifier $verifier */
    $verifier = app(\App\Services\RecaptchaVerifier::class);
@endphp

{{--
    Compact reCAPTCHA disclosure, meant to sit directly above a password field.

    The wording follows the configured version: v3 draws no checkbox at all,
    so this line is the only visible sign of the protection, whereas v2 points
    at the checkbox rendered below the field.

    It renders nothing at all when the protection is disabled or
    unconfigured, so it never claims a guarantee the server does not
    actually enforce.

    Google requires a privacy policy and terms link alongside any such
    disclosure; both are rendered on the right.
--}}
@if ($verifier->enabled())
    <style>
        .aqua-rc-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.35rem 0.5rem;
            padding: 0.4rem 0.65rem;
            margin-bottom: 0.4rem;
            border-radius: 0.55rem;
            border: 1px solid rgba(34, 211, 238, 0.18);
            background: rgba(34, 211, 238, 0.05);
            font-size: 0.68rem;
            line-height: 1.4;
        }

        .aqua-rc-line__mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.15rem;
            height: 1.15rem;
            border-radius: 0.35rem;
            background: rgba(34, 211, 238, 0.16);
            color: #22d3ee;
            flex: none;
        }

        .aqua-rc-line__label {
            color: rgba(240, 253, 250, 0.55);
            font-weight: 500;
        }

        .aqua-rc-line__label strong {
            color: rgba(240, 253, 250, 0.82);
            font-weight: 700;
        }

        .aqua-rc-line__hint {
            color: rgba(240, 253, 250, 0.35);
            font-weight: 400;
        }

        .aqua-rc-line__spacer {
            flex: 1 1 auto;
            min-width: 0.5rem;
        }

        .aqua-rc-line__link {
            color: rgba(34, 211, 238, 0.68);
            text-decoration: none;
            font-weight: 500;
            white-space: nowrap;
        }

        .aqua-rc-line__link:hover {
            color: #22d3ee;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        /* Keep the row compact on narrow screens. */
        @media (max-width: 420px) {
            .aqua-rc-line__hint { display: none; }
            .aqua-rc-line__spacer { display: none; }
        }
    </style>

    <div {{ $attributes->merge(['class' => 'aqua-rc-line']) }}
         role="note"
         aria-label="Formulaire protégé par Google reCAPTCHA">

        <span class="aqua-rc-line__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none"
                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </span>

        <span class="aqua-rc-line__label">
            Protégé par <strong>reCAPTCHA {{ $verifier->version() }}</strong>
            <span class="aqua-rc-line__hint">
                — {{ $verifier->isV2() ? 'case à cocher sous le mot de passe' : 'vérification automatique' }}
            </span>
        </span>

        <span class="aqua-rc-line__spacer" aria-hidden="true"></span>

        <a class="aqua-rc-line__link"
           href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">Conditions</a>
        <a class="aqua-rc-line__link"
           href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Confidentialité</a>
    </div>
@endif
