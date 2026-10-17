<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    /**
     * Afficher la page de configuration 2FA.
     */
    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        return view('profile.two-factor', [
            'user' => $user,
            'enabled' => $user->two_factor_confirmed_at !== null,
        ]);
    }

    /**
     * Activer 2FA : générer le secret et afficher le QR code.
     */
    public function enable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('profile.two-factor')
                ->with('info', 'L\'authentification à deux facteurs est déjà activée.');
        }

        // Ne générer le secret que s'il n'en existe pas déjà un en attente :
        // en régénérer à chaque appel invaliderait le QR code que l'utilisateur
        // vient de scanner dans son application d'authentification (les codes
        // suivants seraient alors refusés, puisque vérifiés contre l'ancien secret).
        if (! $user->two_factor_secret) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();

            $user->forceFill([
                'two_factor_secret' => encrypt($secret),
                'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            ])->save();

            AuditLog::record('two_factor_setup_started', 'User', $user->id, [
                'ip' => $request->ip(),
            ], $user);
        }

        return redirect()->route('profile.two-factor.confirm')
            ->with('success', 'Scannez le QR code avec votre application d\'authentification.');
    }

    /**
     * Afficher le QR code pour confirmer l'activation.
     */
    public function showConfirm(Request $request)
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        if (! $user->two_factor_secret || $user->two_factor_confirmed_at) {
            return redirect()->route('profile.two-factor');
        }

        $secret = decrypt($user->two_factor_secret);
        $qrCodeUrl = $this->generateQrCodeSvg($user->email, $secret);
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        return view('profile.two-factor-confirm', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeUrl,
            'secret' => $secret,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Confirmer l'activation du 2FA avec un code valide.
     */
    public function confirm(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (! $user->two_factor_secret) {
            return redirect()->route('profile.two-factor')
                ->with('error', 'Aucune configuration 2FA en cours.');
        }

        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (! $google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors([
                'code' => 'Code invalide. Vérifiez le code dans votre application.',
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        AuditLog::record('two_factor_enabled', 'User', $user->id, [
            'ip' => $request->ip(),
        ], $user);

        return redirect()->route('profile.two-factor')
            ->with('success', 'Authentification à deux facteurs activée avec succès ! Conservez vos codes de récupération en lieu sûr.');
    }

    /**
     * Désactiver le 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Mot de passe incorrect.',
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        AuditLog::record('two_factor_disabled', 'User', $user->id, [
            'ip' => $request->ip(),
        ], $user);

        return redirect()->route('profile.two-factor')
            ->with('success', 'Authentification à deux facteurs désactivée.');
    }

    /**
     * Régénérer les codes de récupération.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        if (! $user->two_factor_confirmed_at) {
            return redirect()->route('profile.two-factor')
                ->with('error', '2FA non activé.');
        }

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ])->save();

        AuditLog::record('two_factor_recovery_codes_regenerated', 'User', $user->id, [
            'ip' => $request->ip(),
        ], $user);

        return redirect()->route('profile.two-factor')
            ->with('success', 'Codes de récupération régénérés. Notez-les en lieu sûr.');
    }

    /**
     * Afficher les codes de récupération.
     */
    public function showRecoveryCodes(Request $request)
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        if (! $user->two_factor_confirmed_at || ! $user->two_factor_recovery_codes) {
            return redirect()->route('profile.two-factor');
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        return view('profile.two-factor-recovery', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Générer 8 codes de récupération aléatoires.
     */
    private function generateRecoveryCodes(): array
    {
        return Collection::times(8, function () {
            return strtoupper(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(8))), 0, 10));
        })->toArray();
    }

    /**
     * Générer le QR code SVG pour l'application d'authentification.
     *
     * Respecte le format « Google Key URI Format » : le label et le paramètre
     * issuer doivent être URL-encodés, sinon certaines applications
     * d'authentification échouent silencieusement au scan du QR code.
     */
    private function generateQrCodeSvg(string $email, string $secret): string
    {
        $appName = config('app.name', 'AquaSecure');
        $label = rawurlencode($appName) . ':' . rawurlencode($email);

        $qrCodeUrl = 'otpauth://totp/' . $label . '?' . http_build_query([
            'secret' => $secret,
            'issuer' => $appName,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ], '', '&', PHP_QUERY_RFC3986);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }
}
