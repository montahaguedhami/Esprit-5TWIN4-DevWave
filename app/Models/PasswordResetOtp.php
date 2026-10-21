<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * Stores a single password-recovery request.
 *
 * Security invariants enforced here:
 *  - The OTP and the reset token are only ever stored as hashes.
 *  - An OTP is invalidated after it expires or after too many attempts.
 *  - A reset token is single-use and short lived.
 */
class PasswordResetOtp extends Model
{
    /** Lifetime of the OTP sent by email (minutes). */
    public const OTP_TTL_MINUTES = 1;

    /** Lifetime of the proof token issued after a successful OTP check (minutes). */
    public const TOKEN_TTL_MINUTES = 30;

    /** Maximum number of OTP verification attempts per request. */
    public const MAX_ATTEMPTS = 5;

    protected $fillable = [
        'user_id',
        'email',
        'otp_hash',
        'otp_expires_at',
        'attempts',
        'max_attempts',
        'reset_token_hash',
        'reset_token_expires_at',
        'verified_at',
        'consumed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at'           => 'datetime',
            'reset_token_expires_at'   => 'datetime',
            'verified_at'              => 'datetime',
            'consumed_at'              => 'datetime',
            'attempts'                 => 'integer',
            'max_attempts'             => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Generation
     |_______________________________________________________________________*/

    /**
     * Create a fresh recovery request for the given user, invalidating any
     * previous outstanding request for the same address.
     *
     * Returns the request together with the OTP in clear text — the caller
     * is responsible for emailing it and must never persist or log it.
     */
    public static function issue(User $user, string $ip = null, string $userAgent = null): array
    {
        // Invalidate every previous request for this address (single active request).
        static::where('email', $user->email)
            ->whereNull('consumed_at')
            ->update([
                'otp_expires_at'       => now()->subMinute(),
                'reset_token_hash'     => null,
                'reset_token_expires_at' => null,
            ]);

        $otp = static::generateOtp();

        $request = static::create([
            'user_id'       => $user->id,
            'email'         => $user->email,
            'otp_hash'      => Hash::make($otp),
            'otp_expires_at'=> now()->addMinutes(static::OTP_TTL_MINUTES),
            'attempts'      => 0,
            'max_attempts'  => static::MAX_ATTEMPTS,
            'ip_address'    => $ip,
            // Colonne VARCHAR(191) (Schema::defaultStringLength) : tronqué par caractère, sans couper un accent
            'user_agent'    => $userAgent !== null ? mb_substr($userAgent, 0, 191) : null,
        ]);

        return [$request, $otp];
    }

    /**
     * Cryptographically secure 6-digit OTP (leading zeros preserved).
     */
    public static function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Lookup helpers
     |_______________________________________________________________________*/

    /**
     * The active (not consumed) request for an address, if any.
     */
    public static function findActive(string $email): ?self
    {
        return static::where('email', strtolower(trim($email)))
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  State queries
     |_______________________________________________________________________*/

    public function isExpired(): bool
    {
        return $this->otp_expires_at === null || $this->otp_expires_at->isPast();
    }

    public function hasExhaustedAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasValidResetToken(): bool
    {
        return $this->reset_token_hash !== null
            && $this->reset_token_expires_at !== null
            && $this->reset_token_expires_at->isFuture();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Transitions
     |_______________________________________________________________________*/

    /**
     * Verify an OTP candidate. Returns true when it matches.
     * Increments the attempt counter on every call.
     */
    public function verifyOtp(string $candidate): bool
    {
        if ($this->isConsumed() || $this->isExpired() || $this->hasExhaustedAttempts()) {
            return false;
        }

        $this->increment('attempts');

        if (! Hash::check($candidate, $this->otp_hash)) {
            return false;
        }

        return true;
    }

    /**
     * Mark the OTP as validated and mint the single-use reset token.
     * Returns the raw token — the caller must hand it to the user and
     * must never store it in clear text.
     */
    public function mintResetToken(): string
    {
        $raw = bin2hex(random_bytes(32));

        $this->forceFill([
            'reset_token_hash'       => hash('sha256', $raw),
            'reset_token_expires_at' => now()->addMinutes(static::TOKEN_TTL_MINUTES),
            'verified_at'            => now(),
        ])->save();

        return $raw;
    }

    /**
     * Consume the request once the password has been changed.
     */
    public function markConsumed(): void
    {
        $this->forceFill([
            'consumed_at'            => now(),
            'reset_token_hash'       => null,
            'reset_token_expires_at' => null,
            'otp_hash'               => 'invalidated',
        ])->save();
    }

    /**
     * Invalidate the request (used when the process is aborted).
     */
    public function invalidate(): void
    {
        $this->forceFill([
            'otp_expires_at'         => now()->subMinute(),
            'reset_token_hash'       => null,
            'reset_token_expires_at' => null,
        ])->save();
    }
}
