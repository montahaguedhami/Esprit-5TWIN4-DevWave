<?php

namespace Tests\Unit;

use App\Services\RecaptchaVerifier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Vérification reCAPTCHA côté serveur (RecaptchaVerifier). L'appel à
 * l'API siteverify de Google est toujours simulé par Http::fake().
 */
class RecaptchaVerifierTest extends TestCase
{
    private const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    private RecaptchaVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('recaptcha', [
            'version'           => 'v3',
            'enabled'           => true,
            'site_key'          => 'site-key-test',
            'secret_key'        => 'secret-key-test',
            'min_score'         => 0.5,
            'enforce_action'    => true,
            'expected_hostname' => null,
        ]);

        $this->verifier = new RecaptchaVerifier();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Configuration
     |_______________________________________________________________________*/

    public function test_configuration_accessors(): void
    {
        $this->assertTrue($this->verifier->enabled());
        $this->assertTrue($this->verifier->isConfigured());
        $this->assertSame('site-key-test', $this->verifier->siteKey());
        $this->assertSame(0.5, $this->verifier->minScore());
        $this->assertSame('v3', $this->verifier->version());
        $this->assertFalse($this->verifier->isV2());

        config()->set('recaptcha.version', 'v2');
        $this->assertSame('v2', $this->verifier->version());
        $this->assertTrue($this->verifier->isV2());

        // Une valeur inconnue retombe sur v3 (jamais de désactivation par faute de frappe).
        config()->set('recaptcha.version', 'v4');
        $this->assertSame('v3', $this->verifier->version());
    }

    public function test_kill_switch_disables_the_check_without_calling_google(): void
    {
        Http::fake();
        config()->set('recaptcha.enabled', false);

        $this->assertFalse($this->verifier->enabled());
        $this->assertSame(['ok' => true, 'error' => null, 'score' => null], $this->verifier->verify(null, 'login'));

        Http::assertNothingSent();
    }

    public function test_missing_keys_are_skipped_outside_production_with_a_warning(): void
    {
        Http::fake();
        Log::spy();
        config()->set('recaptcha.secret_key', null);

        $this->assertFalse($this->verifier->enabled());
        $this->assertTrue($this->verifier->passes(null, 'register'));

        Log::shouldHaveReceived('warning')->once();
        Http::assertNothingSent();
    }

    public function test_missing_keys_fail_closed_in_production(): void
    {
        Http::fake();
        Log::spy();
        config()->set('recaptcha.site_key', '');
        $this->app['env'] = 'production';

        $result = $this->verifier->verify('jeton', 'login');

        $this->assertFalse($result['ok']);
        $this->assertSame('captcha_not_configured', $result['error']);
        Log::shouldHaveReceived('error')->once();
        Http::assertNothingSent();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Vérification auprès de Google
     |_______________________________________________________________________*/

    public function test_missing_token_is_refused_without_calling_google(): void
    {
        Http::fake();

        $this->assertSame('captcha_missing', $this->verifier->verify('', 'login')['error']);
        $this->assertSame('captcha_missing', $this->verifier->verify(null, 'login')['error']);

        Http::assertNothingSent();
    }

    public function test_valid_v3_token_is_accepted_and_sent_with_the_secret(): void
    {
        $this->fakeGoogle(['success' => true, 'score' => 0.9, 'action' => 'login', 'hostname' => 'aquasecure.tn']);

        $result = $this->verifier->verify('jeton-valide', 'login');

        $this->assertSame(['ok' => true, 'error' => null, 'score' => 0.9], $result);

        Http::assertSent(fn (Request $request) => $request->url() === self::ENDPOINT
            && $request['secret'] === 'secret-key-test'
            && $request['response'] === 'jeton-valide'
            && $request->isForm());
    }

    public function test_token_rejected_by_google_is_invalid(): void
    {
        $this->fakeGoogle(['success' => false, 'error-codes' => ['invalid-input-response']]);

        $this->assertSame('captcha_invalid', $this->verifier->verify('jeton', 'login')['error']);
    }

    public function test_http_error_or_network_failure_fails_closed(): void
    {
        Http::fake([self::ENDPOINT => Http::response('Erreur', 500)]);
        $this->assertSame('captcha_unreachable', $this->verifier->verify('jeton', 'login')['error']);

        Log::spy();
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake([self::ENDPOINT => fn () => throw new ConnectionException('Connexion refusée')]);
        $result = $this->verifier->verify('jeton', 'login');

        $this->assertFalse($result['ok']);
        $this->assertSame('captcha_unreachable', $result['error']);
        Log::shouldHaveReceived('error')->once();
    }

    public function test_hostname_pinning_blocks_tokens_from_another_domain(): void
    {
        config()->set('recaptcha.expected_hostname', 'aquasecure.tn');

        $this->fakeGoogle(['success' => true, 'score' => 0.9, 'action' => 'login', 'hostname' => 'pirate.example']);
        $this->assertSame('captcha_hostname_mismatch', $this->verifier->verify('jeton', 'login')['error']);

        $this->fakeGoogle(['success' => true, 'score' => 0.9, 'action' => 'login', 'hostname' => 'aquasecure.tn']);
        $this->assertTrue($this->verifier->passes('jeton', 'login'));
    }

    public function test_v3_action_must_match_the_form(): void
    {
        $this->fakeGoogle(['success' => true, 'score' => 0.9, 'action' => 'register']);
        $this->assertSame('captcha_action_mismatch', $this->verifier->verify('jeton', 'login')['error']);

        // Contrôle de l'action désactivable par configuration.
        config()->set('recaptcha.enforce_action', false);
        $this->assertTrue($this->verifier->passes('jeton', 'login'));
    }

    public function test_v3_low_score_is_refused(): void
    {
        $this->fakeGoogle(['success' => true, 'score' => 0.2, 'action' => 'login']);

        $this->assertSame(
            ['ok' => false, 'error' => 'captcha_low_score', 'score' => 0.2],
            $this->verifier->verify('jeton', 'login'),
        );

        // Le seuil est configurable.
        config()->set('recaptcha.min_score', 0.1);
        $this->assertTrue($this->verifier->passes('jeton', 'login'));
    }

    public function test_v2_success_is_the_whole_verdict(): void
    {
        config()->set('recaptcha.version', 'v2');

        // v2 ne renvoie ni score ni action : ils ne doivent pas être exigés.
        $this->fakeGoogle(['success' => true, 'hostname' => 'aquasecure.tn']);

        $this->assertSame(['ok' => true, 'error' => null, 'score' => null], $this->verifier->verify('jeton', 'login'));
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Messages
     |_______________________________________________________________________*/

    public function test_each_error_has_a_user_facing_message(): void
    {
        $this->assertSame('Veuillez confirmer que vous n\'êtes pas un robot.', $this->verifier->messageFor('captcha_missing'));
        $this->assertSame('Votre navigateur n\'a pas pu être vérifié. Veuillez réessayer.', $this->verifier->messageFor('captcha_low_score'));
        $this->assertSame('La vérification anti-robot est invalide. Veuillez recharger la page.', $this->verifier->messageFor('captcha_action_mismatch'));
        $this->assertSame('La vérification anti-robot est invalide pour ce domaine.', $this->verifier->messageFor('captcha_hostname_mismatch'));
        $this->assertSame('La protection anti-robot n\'est pas configurée sur ce serveur.', $this->verifier->messageFor('captcha_not_configured'));
        $this->assertSame('Le service de vérification est momentanément indisponible. Veuillez réessayer.', $this->verifier->messageFor('captcha_unreachable'));

        $generique = 'La vérification anti-robot a échoué. Veuillez réessayer.';
        $this->assertSame($generique, $this->verifier->messageFor('captcha_invalid'));
        $this->assertSame($generique, $this->verifier->messageFor('code_inconnu'));
        $this->assertSame($generique, $this->verifier->messageFor(null));
    }

    private function fakeGoogle(array $payload): void
    {
        // Remplace toute simulation précédente : seule la dernière réponse compte.
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake([self::ENDPOINT => Http::response($payload)]);
    }
}
