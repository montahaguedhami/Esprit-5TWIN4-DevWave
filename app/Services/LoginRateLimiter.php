<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Centralised brute-force protection.
 *
 * Rules (shared by the web login, the API login and the OTP endpoints):
 *  - 5 failed attempts on the same identifier are tolerated.
 *  - A 30 second lockout is then enforced server side.
 *  - A successful authentication clears the counter.
 *  - The identifier is normalised (trimmed + lowercased) so that changing
 *    the letter case does not reset the counter.
 *  - A secondary per-IP counter absorbs distributed attacks targeting
 *    many different accounts from a single source.
 *
 * Backed by the Laravel cache (database/redis), never by a PHP variable
 * nor by anything client side.
 */
class LoginRateLimiter
{
    public const MAX_ATTEMPTS = 5;
    public const LOCKOUT_SECONDS = 30;

    /** Secondary guard: many distinct identifiers from one IP. */
    public const IP_MAX_ATTEMPTS = 20;
    public const IP_LOCKOUT_SECONDS = 60;

    /* ────────────────────────────────────────────────────────────────────────
     |  Keys
     |_______________________________________________________________________*/

    /**
     * Normalise an identifier so case variations share the same counter.
     */
    public static function normalize(string $identifier): string
    {
        return strtolower(trim($identifier));
    }

    private function identifierKey(string $identifier, string $ip): string
    {
        return sprintf('login:%s:%s', hash('sha256', self::normalize($identifier)), hash('sha256', $ip));
    }

    private function ipKey(string $ip): string
    {
        return sprintf('login-ip:%s', hash('sha256', $ip));
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Public API
     |_______________________________________________________________________*/

    /**
     * Returns the number of seconds remaining before a new attempt is
     * allowed, or 0 when the identifier is not currently locked.
     */
    public function secondsRemaining(string $identifier, string $ip): int
    {
        $identifierKey = $this->identifierKey($identifier, $ip);

        if (RateLimiter::tooManyAttempts($identifierKey, self::MAX_ATTEMPTS)) {
            return max(1, (int) RateLimiter::availableIn($identifierKey));
        }

        $ipKey = $this->ipKey($ip);
        if (RateLimiter::tooManyAttempts($ipKey, self::IP_MAX_ATTEMPTS)) {
            return max(1, (int) RateLimiter::availableIn($ipKey));
        }

        return 0;
    }

    /**
     * True when a new authentication attempt must be refused outright,
     * before any credential check is performed.
     */
    public function isBlocked(string $identifier, string $ip): bool
    {
        return $this->secondsRemaining($identifier, $ip) > 0;
    }

    /**
     * Register a failed authentication attempt.
     */
    public function hit(string $identifier, string $ip): void
    {
        RateLimiter::hit($this->identifierKey($identifier, $ip), self::LOCKOUT_SECONDS);
        RateLimiter::hit($this->ipKey($ip), self::IP_LOCKOUT_SECONDS);
    }

    /**
     * Clear both counters after a successful authentication.
     */
    public function clear(string $identifier, string $ip): void
    {
        RateLimiter::clear($this->identifierKey($identifier, $ip));
        RateLimiter::clear($this->ipKey($ip));
    }

    /**
     * Human readable, time-accurate message for web/API responses.
     */
    public function message(string $identifier, string $ip): string
    {
        $seconds = $this->secondsRemaining($identifier, $ip);

        return sprintf(
            'Trop de tentatives de connexion. Veuillez réessayer dans %d seconde%s.',
            $seconds,
            $seconds > 1 ? 's' : ''
        );
    }

    /**
     * Remaining attempts before the lockout kicks in (for UI feedback).
     */
    public function remainingAttempts(string $identifier, string $ip): int
    {
        $attempts = (int) RateLimiter::attempts($this->identifierKey($identifier, $ip));

        return max(0, self::MAX_ATTEMPTS - $attempts);
    }
}
