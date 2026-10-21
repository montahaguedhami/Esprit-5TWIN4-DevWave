<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google reCAPTCHA v3 verification.
 *
 * The token is always verified server side against Google's official
 * endpoint. A score sent by the browser is never trusted, and a
 * configuration error is never silently treated as a success.
 *
 * Behaviour by environment:
 *  - production + missing keys  -> verification FAILS (no silent bypass)
 *  - local/testing + missing keys -> verification is skipped, a warning is logged
 *  - explicit disable (RECAPTCHA_ENABLED=false) -> skipped everywhere
 */
class RecaptchaVerifier
{
    private const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    public function enabled(): bool
    {
        // Explicit kill-switch (tests, disabled environments).
        if (config('recaptcha.enabled') === false) {
            return false;
        }

        return $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return filled(config('recaptcha.site_key')) && filled(config('recaptcha.secret_key'));
    }

    /**
     * Widget version currently wired up: "v2" (visible checkbox) or "v3" (invisible).
     */
    public function version(): string
    {
        return config('recaptcha.version') === 'v2' ? 'v2' : 'v3';
    }

    public function isV2(): bool
    {
        return $this->version() === 'v2';
    }

    public function siteKey(): ?string
    {
        return config('recaptcha.site_key');
    }

    public function minScore(): float
    {
        return (float) config('recaptcha.min_score', 0.5);
    }

    /**
     * Verify a reCAPTCHA token for a given action.
     *
     * @param  string|null  $token  the g-recaptcha-response value
     * @param  string  $action  the expected action name (login, register, ...)
     * @return array{ok: bool, error: ?string, score: ?float}
     */
    public function verify(?string $token, string $action): array
    {
        // Kill-switch explicitly turned off.
        if (config('recaptcha.enabled') === false) {
            return $this->ok(null);
        }

        // Not configured.
        if (! $this->isConfigured()) {
            // Never allow a missing configuration to pass silently in production.
            if (app()->environment('production')) {
                Log::error('reCAPTCHA is not configured — rejecting the request in production.', [
                    'action' => $action,
                ]);

                return $this->fail('captcha_not_configured');
            }

            Log::warning('reCAPTCHA is not configured — verification skipped (non-production).', [
                'action' => $action,
            ]);

            return $this->ok(null);
        }

        if (blank($token)) {
            return $this->fail('captcha_missing');
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::ENDPOINT, [
                    'secret'   => config('recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);
        } catch (\Throwable $e) {
            // Network failure talking to Google: fail closed, never open.
            Log::error('reCAPTCHA verification unreachable.', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);

            return $this->fail('captcha_unreachable');
        }

        if ($response->failed()) {
            return $this->fail('captcha_unreachable');
        }

        $payload = $response->json() ?? [];

        if (empty($payload['success'])) {
            return $this->fail('captcha_invalid');
        }

        // Optional hostname pinning (protects against token replay from another domain).
        $expectedHost = config('recaptcha.expected_hostname');
        if (filled($expectedHost) && isset($payload['hostname']) && $payload['hostname'] !== $expectedHost) {
            return $this->fail('captcha_hostname_mismatch');
        }

        $score = isset($payload['score']) ? (float) $payload['score'] : null;

        // v2 answers with a single boolean: there is no score and no action to
        // enforce, so `success` is the whole verdict.
        //
        // v3 adds both, and they are exactly what separates a real visitor from
        // a scripted call, so they are enforced whenever Google returns them.
        if (! $this->isV2()) {
            // Enforce the action name to block token reuse across forms.
            if (config('recaptcha.enforce_action', true)) {
                $returnedAction = $payload['action'] ?? null;
                if ($returnedAction !== $action) {
                    return $this->fail('captcha_action_mismatch');
                }
            }

            if ($score !== null && $score < $this->minScore()) {
                return $this->fail('captcha_low_score', $score);
            }
        }

        return $this->ok($score);
    }

    /**
     * Convenience boolean wrapper around verify().
     */
    public function passes(?string $token, string $action): bool
    {
        return $this->verify($token, $action)['ok'];
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Result builders
     |_______________________________________________________________________*/

    private function ok(?float $score): array
    {
        return ['ok' => true, 'error' => null, 'score' => $score];
    }

    private function fail(string $error, ?float $score = null): array
    {
        return ['ok' => false, 'error' => $error, 'score' => $score];
    }

    /**
     * User facing message for a given error code.
     */
    public function messageFor(?string $error): string
    {
        return match ($error) {
            'captcha_missing'          => 'Veuillez confirmer que vous n\'êtes pas un robot.',
            'captcha_invalid'          => 'La vérification anti-robot a échoué. Veuillez réessayer.',
            'captcha_low_score'        => 'Votre navigateur n\'a pas pu être vérifié. Veuillez réessayer.',
            'captcha_action_mismatch'  => 'La vérification anti-robot est invalide. Veuillez recharger la page.',
            'captcha_hostname_mismatch'=> 'La vérification anti-robot est invalide pour ce domaine.',
            'captcha_not_configured'   => 'La protection anti-robot n\'est pas configurée sur ce serveur.',
            'captcha_unreachable'      => 'Le service de vérification est momentanément indisponible. Veuillez réessayer.',
            default                    => 'La vérification anti-robot a échoué. Veuillez réessayer.',
        };
    }
}
