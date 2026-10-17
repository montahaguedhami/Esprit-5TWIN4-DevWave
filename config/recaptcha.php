<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA
    |--------------------------------------------------------------------------
    |
    | Keys are read from the environment — never commit real values.
    | Obtain them on https://www.google.com/recaptcha/admin
    |
    | RECAPTCHA_VERSION=v2      checkbox "Je ne suis pas un robot" (visible)
    | RECAPTCHA_VERSION=v3      invisible, score-based (default)
    |
    | RECAPTCHA_SITE_KEY=
    | RECAPTCHA_SECRET_KEY=
    | RECAPTCHA_MIN_SCORE=0.5   v3 only
    |
    | A v2 key and a v3 key are NOT interchangeable: Google issues one key per
    | type, and calling grecaptcha.execute() with a v2 key throws. Always keep
    | RECAPTCHA_VERSION in line with the key type registered in the console.
    |
    */

    /*
    | Widget version.
    |
    | v2 renders the visible "I'm not a robot" checkbox and returns a single
    | boolean — there is no score and no action to enforce.
    | v3 stays invisible and adds a score (0.0 bot … 1.0 human) plus an action
    | name that must match the form it was issued for.
    |
    | Unknown values fall back to v3 so a typo can never disable the checks.
    */
    'version' => env('RECAPTCHA_VERSION', 'v3') === 'v2' ? 'v2' : 'v3',

    /*
    | Kill-switch for the whole protection.
    |
    | It is normalised through FILTER_VALIDATE_BOOLEAN because env() may hand
    | back a boolean, "true"/"false", "1"/"0" or even an empty string: PHPUnit
    | turns <env name="X" value="false"/> into an empty string (its XML loader
    | casts "false" to a PHP boolean, which then string-interpolates to "").
    | A plain (bool) cast would be wrong here: (bool) "false" is TRUE.
    |
    | Production stays fail-closed: the default is true and a missing
    | configuration is rejected rather than silently bypassed (see
    | RecaptchaVerifier::verify()).
    */
    'enabled' => filter_var(env('RECAPTCHA_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'site_key'   => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),

    // Minimum score accepted for a v3 verification (0.0 = bot, 1.0 = human).
    'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),

    // Refuse tokens whose returned "action" does not match the expected one.
    'enforce_action' => (bool) env('RECAPTCHA_ENFORCE_ACTION', true),

    // Optional: pin verification to a hostname (blocks cross-domain token replay).
    'expected_hostname' => env('RECAPTCHA_HOSTNAME'),

];
