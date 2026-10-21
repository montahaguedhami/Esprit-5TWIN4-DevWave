<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\SendPasswordResetOtp;
use App\Services\LoginRateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Three step password recovery:
 *   A) request an OTP by email
 *   B) verify the OTP
 *   C) set a new password with the proof minted at step B
 *
 * Nothing here trusts a client side flag: step C requires the random
 * single-use token issued by step B, stored hashed server side.
 */
class PasswordResetController extends Controller
{
    public function __construct(
        private readonly LoginRateLimiter $limiter,
    ) {}

    /* ────────────────────────────────────────────────────────────────────────
     |  Step A — request the OTP
     |_______________________________________________________________________*/

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $ip    = (string) $request->ip();

        // Anti-abuse: limit how often a single address can ask for a code.
        $throttleKey = 'password-reset-request:'.md5($email);
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = max(1, RateLimiter::availableIn($throttleKey));

            return back()->withErrors([
                'email' => sprintf('Trop de demandes. Veuillez réessayer dans %d seconde%s.', $seconds, $seconds > 1 ? 's' : ''),
            ]);
        }

        $user = User::where('email', $email)->first();

        // Always answer generically: never disclose whether an account exists.
        $generic = back()->with('status', 'Si un compte est associé à cette adresse, un code de vérification a été envoyé.');

        if (! $user) {
            RateLimiter::hit($throttleKey, 60);

            return $generic;
        }

        // Never send a code to a deactivated account.
        if (! $user->is_active) {
            RateLimiter::hit($throttleKey, 60);

            return $generic;
        }

        try {
            [$resetRequest, $otp] = PasswordResetOtp::issue($user, $ip, (string) $request->userAgent());

            Notification::send($user, new SendPasswordResetOtp($otp, $resetRequest));

            AuditLog::record('password_reset_requested', 'User', $user->id, [
                'ip'         => $ip,
                'request_id' => $resetRequest->id,
            ], $user);
        } catch (\Throwable $e) {
            report($e);

            // Never leak internal failures to the visitor.
            return back()->with('status', 'Si un compte est associé à cette adresse, un code de vérification a été envoyé.');
        }

        RateLimiter::hit($throttleKey, 60);

        // Carry the (non sensitive) request id through the flow.
        $request->session()->put('password_reset_request_id', $resetRequest->id);

        return redirect()->route('password.verify.otp');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Step B — verify the OTP
     |_______________________________________________________________________*/

    public function showOtpForm(Request $request): RedirectResponse|\Illuminate\Contracts\View\View
    {
        if (! $request->session()->has('password_reset_request_id')) {
            return redirect()->route('auth.forgot-password');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $requestId = $request->session()->get('password_reset_request_id');
        $record    = $requestId ? PasswordResetOtp::find($requestId) : null;

        if (! $record || $record->isConsumed()) {
            $request->session()->forget('password_reset_request_id');

            return redirect()->route('auth.forgot-password')
                ->withErrors(['otp' => 'Cette demande n\'est plus valide. Refaites une demande.']);
        }

        if ($record->isVerified() && $record->hasValidResetToken()) {
            // Already validated — move on (idempotent, no new token minted).
            return redirect()->route('password.reset.form');
        }

        if ($record->isExpired()) {
            $record->invalidate();

            return redirect()->route('auth.forgot-password')
                ->withErrors(['otp' => 'Le code a expiré. Demandez un nouveau code.']);
        }

        if ($record->hasExhaustedAttempts()) {
            $record->invalidate();

            return redirect()->route('auth.forgot-password')
                ->withErrors(['otp' => 'Nombre d\'essais dépassé. Demandez un nouveau code.']);
        }

        if (! $record->verifyOtp($validated['otp'])) {
            $remaining = max(0, $record->max_attempts - $record->attempts);

            if ($record->hasExhaustedAttempts()) {
                $record->invalidate();

                return redirect()->route('auth.forgot-password')
                    ->withErrors(['otp' => 'Nombre d\'essais dépassé. Demandez un nouveau code.']);
            }

            return back()->withErrors([
                'otp' => sprintf('Code incorrect. %d essai%s restant%s.', $remaining, $remaining > 1 ? 's' : '', $remaining > 1 ? 's' : ''),
            ]);
        }

        // Success: mint the single-use reset token.
        $rawToken = $record->mintResetToken();

        AuditLog::record('password_reset_otp_verified', 'User', $record->user_id, [
            'ip'         => (string) $request->ip(),
            'request_id' => $record->id,
        ], $record->user);

        // Store the proof server side in the session (never a query parameter).
        $request->session()->put('password_reset_token', hash('sha256', $rawToken));
        $request->session()->forget('password_reset_request_id');

        return redirect()->route('password.reset.form');
    }

    public function resend(Request $request): RedirectResponse
    {
        $requestId = $request->session()->get('password_reset_request_id');
        $record    = $requestId ? PasswordResetOtp::find($requestId) : null;

        if (! $record || $record->isConsumed()) {
            return redirect()->route('auth.forgot-password')
                ->with('status', 'Si un compte est associé à cette adresse, un code de vérification a été envoyé.');
        }

        $throttleKey = 'password-reset-resend:'.md5($record->email);
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = max(1, RateLimiter::availableIn($throttleKey));

            return back()->withErrors([
                'otp' => sprintf('Trop de renvois. Veuillez réessayer dans %d seconde%s.', $seconds, $seconds > 1 ? 's' : ''),
            ]);
        }

        if (! $record->user || ! $record->user->is_active) {
            return redirect()->route('auth.forgot-password');
        }

        try {
            [$fresh, $otp] = PasswordResetOtp::issue($record->user, (string) $request->ip(), (string) $request->userAgent());
            Notification::send($record->user, new SendPasswordResetOtp($otp, $fresh));

            $request->session()->put('password_reset_request_id', $fresh->id);
            $request->session()->forget('password_reset_token');
        } catch (\Throwable $e) {
            report($e);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->with('status', 'Un nouveau code a été envoyé.');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Step C — set a new password
     |_______________________________________________________________________*/

    public function showResetForm(Request $request): RedirectResponse|\Illuminate\Contracts\View\View
    {
        $tokenHash = $request->session()->get('password_reset_token');

        if (! $tokenHash || ! $this->findVerifiedRecord($tokenHash)) {
            return redirect()->route('auth.forgot-password');
        }

        return view('auth.reset-password');
    }

    public function reset(Request $request): RedirectResponse
    {
        $tokenHash = $request->session()->get('password_reset_token');

        $record = $tokenHash ? $this->findVerifiedRecord($tokenHash) : null;

        if (! $record) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['password' => 'Cette session de réinitialisation n\'est plus valide. Refaites une demande.']);
        }

        $validated = $request->validate([
            'password' => [
                'required', 'string', 'confirmed',
                'min:12',
                'regex:/[a-z]/',       // lowercase
                'regex:/[A-Z]/',       // uppercase
                'regex:/[0-9]/',       // digit
                'regex:/[^A-Za-z0-9]/',// symbol
            ],
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
            'password.min'   => 'Le mot de passe doit contenir au moins 12 caractères.',
        ]);

        $user = $record->user;

        if (! $user) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['password' => 'Compte introuvable.']);
        }

        if (! $user->is_active) {
            return redirect()->route('auth.forgot-password')
                ->withErrors(['password' => 'Ce compte a été désactivé.']);
        }

        DB::transaction(function () use ($validated, $user, $record) {
            $user->forceFill(['password' => Hash::make($validated['password'])])->save();

            // Invalidate the request and every other outstanding one.
            $record->markConsumed();
            PasswordResetOtp::where('email', $user->email)
                ->whereNull('consumed_at')
                ->update([
                    'reset_token_hash'       => null,
                    'reset_token_expires_at' => null,
                ]);

            // Revoke existing credentials.
            $this->revokeSessions($user);
        });

        // Drop the proof from the session.
        $request->session()->forget(['password_reset_token', 'password_reset_request_id']);

        AuditLog::record('password_reset_completed', 'User', $user->id, [
            'ip'         => (string) $request->ip(),
            'request_id' => $record->id,
        ], $user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login')
            ->with('success', 'Votre mot de passe a été réinitialisé. Connectez-vous avec votre nouveau mot de passe.');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Internals
     |_______________________________________________________________________*/

    /**
     * Resolve the active, verified, unconsumed request matching the session proof.
     */
    private function findVerifiedRecord(string $tokenHash): ?PasswordResetOtp
    {
        return PasswordResetOtp::where('reset_token_hash', $tokenHash)
            ->whereNull('consumed_at')
            ->where('reset_token_expires_at', '>', now())
            ->first();
    }

    /**
     * Revoke every session and refresh token belonging to the user.
     */
    private function revokeSessions(User $user): void
    {
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // Table may not exist in some environments — non blocking.
        }

        try {
            if (class_exists(\App\Models\JwtRefreshToken::class)) {
                \App\Models\JwtRefreshToken::where('user_id', $user->id)
                    ->where('revoked', false)
                    ->update(['revoked' => true, 'revoked_at' => now()]);
            }
        } catch (\Throwable $e) {
            // Non blocking.
        }
    }
}
