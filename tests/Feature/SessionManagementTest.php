<?php

namespace Tests\Feature;

use App\Http\Controllers\Settings\SessionController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests de la gestion des sessions actives (Paramètres > Sécurité) :
 * liste des appareils, révocation d'une session, des autres sessions
 * et renouvellement forcé par l'administrateur, pour les stockages
 * « database » et « file ».
 *
 * Les routes de SessionController ne sont plus déclarées dans routes/web.php
 * (voir les deux tests marqués « Bug ») : elles sont recréées ici, à
 * l'identique de leur déclaration d'origine, pour tester le contrôleur.
 */
class SessionManagementTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    private string $sessionDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        $this->sessionDir = sys_get_temp_dir() . '/aqua_sessions_' . uniqid();
        File::ensureDirectoryExists($this->sessionDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->sessionDir);
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Outils
    // -------------------------------------------------------------------------

    /** Déclare les routes du contrôleur telles qu'elles existaient à l'origine. */
    private function registerSessionRoutes(): void
    {
        Route::middleware(['web', 'role:administrateur,manager,technicien,citoyen'])->group(function () {
            Route::get('/settings', [SessionController::class, 'index'])->name('settings.index');
            Route::delete('/settings/sessions/{sessionId}', [SessionController::class, 'revoke'])->name('settings.sessions.revoke');
            Route::post('/settings/sessions/revoke-others', [SessionController::class, 'revokeOthers'])->name('settings.sessions.revoke-others');
        });
        Route::middleware(['web', 'role:administrateur'])
            ->post('/admin/security/renew-sessions', [SessionController::class, 'forceRenewAll'])
            ->name('admin.security.renew-sessions');

        Route::getRoutes()->refreshNameLookups();
        Route::getRoutes()->refreshActionLookups();
    }

    private function useDriver(string $driver): void
    {
        config(['session.driver' => $driver, 'session.files' => $this->sessionDir]);
    }

    private function sessionPayload(User $user): array
    {
        return ['user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->primary_role,
        ]];
    }

    private function dbSession(string $id, ?int $userId, ?string $userAgent = null, ?string $ip = '10.0.0.1', ?int $lastActivity = null): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'payload' => base64_encode(serialize([])),
            'last_activity' => $lastActivity ?? now()->timestamp,
        ]);
    }

    private function fileSession(string $id, array $data, ?int $mtime = null, bool $encrypted = false, bool $json = false): string
    {
        $contents = $json ? json_encode($data) : serialize($data);
        if ($encrypted) {
            $contents = Crypt::encryptString($contents);
        }

        $path = $this->sessionDir . DIRECTORY_SEPARATOR . $id;
        file_put_contents($path, $contents);
        touch($path, $mtime ?? time());

        return $path;
    }

    // -------------------------------------------------------------------------
    // Bugs de câblage des routes
    // -------------------------------------------------------------------------

    public function test_settings_page_is_wired_to_the_session_controller(): void
    {
        $user = $this->createUserWithRole(Role::CITOYEN);
        $this->withoutVite();

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->get('/settings')
            ->assertOk()
            ->assertSee('Sessions actives');
    }

    public function test_admin_security_page_renders_the_renew_sessions_action(): void
    {
        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $this->withoutVite();

        $this->actingAs($admin)->withSession($this->sessionPayload($admin))
            ->get('/admin/security')
            ->assertOk()
            ->assertSee('Forcer renouvellement sessions');
    }

    // -------------------------------------------------------------------------
    // Stockage « database »
    // -------------------------------------------------------------------------

    public function test_database_sessions_are_listed_with_device_labels(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('database');
        $this->withoutVite();

        $user = $this->createUserWithRole(Role::TECHNICIEN);
        $other = $this->createUserWithRole(Role::CITOYEN);
        $current = Str::random(40);

        $this->dbSession($current, $user->id, 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0 Safari/537.36 Edg/120.0');
        $this->dbSession(Str::random(40), $user->id, 'Mozilla/5.0 (Linux; Android 14) Chrome/120.0 Mobile Safari/537.36', '196.203.1.2', now()->subHour()->timestamp);
        $this->dbSession(Str::random(40), $user->id, 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Safari/604.1', null, now()->subHours(2)->timestamp);
        $this->dbSession(Str::random(40), $user->id, 'Mozilla/5.0 (Macintosh; Mac OS X 14_0) Firefox/121.0', '10.0.0.3', now()->subHours(3)->timestamp);
        $this->dbSession(Str::random(40), $user->id, 'Mozilla/5.0 (X11; Linux x86_64) Firefox/121.0', '10.0.0.4', now()->subHours(4)->timestamp);
        $this->dbSession(Str::random(40), $user->id, null, '10.0.0.5', now()->subHours(5)->timestamp);
        $this->dbSession(Str::random(40), $other->id, 'AutreUtilisateur/1.0 (Windows) Firefox/1', '8.8.8.8');

        $response = $this->actingAs($user)
            ->withSession($this->sessionPayload($user))
            ->withCookie(config('session.cookie'), $current)
            ->get('/settings')
            ->assertOk()
            ->assertSee('Windows · Edge')
            ->assertSee('Session actuelle')
            ->assertSee('Android · Chrome')
            ->assertSee('196.203.1.2')
            ->assertSee('iOS · Safari')
            ->assertSee('Adresse inconnue')
            ->assertSee('macOS · Firefox')
            ->assertSee('Linux · Firefox')
            ->assertSee('Appareil inconnu · Navigateur inconnu')
            ->assertSee('Fermer toutes les autres sessions')
            ->assertDontSee('8.8.8.8');

        $sessions = $response->viewData('sessions');
        $this->assertCount(6, $sessions);
        $this->assertSame($current, $sessions->first()['id']);
        $this->assertTrue($sessions->first()['current']);
        $this->assertSame(1, $sessions->where('current', true)->count());
    }

    public function test_user_revokes_one_of_their_database_sessions(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('database');

        $user = $this->createUserWithRole(Role::MANAGER);
        $other = $this->createUserWithRole(Role::CITOYEN);
        $mine = Str::random(40);
        $foreign = Str::random(40);
        $this->dbSession($mine, $user->id);
        $this->dbSession($foreign, $other->id);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->from('/settings')
            ->delete("/settings/sessions/{$mine}")
            ->assertRedirect('/settings')
            ->assertSessionHas('success', 'La session sélectionnée a été révoquée.');

        $this->assertDatabaseMissing('sessions', ['id' => $mine]);

        // La session d'un autre utilisateur n'est jamais supprimée.
        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->delete("/settings/sessions/{$foreign}")
            ->assertRedirect();
        $this->assertDatabaseHas('sessions', ['id' => $foreign]);
    }

    public function test_current_session_and_malformed_ids_cannot_be_revoked(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('database');

        $user = $this->createUserWithRole(Role::CITOYEN);
        $current = Str::random(40);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->withCookie(config('session.cookie'), $current)
            ->delete("/settings/sessions/{$current}")
            ->assertStatus(422);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->delete('/settings/sessions/id.invalide')
            ->assertNotFound();
    }

    public function test_user_closes_all_their_other_database_sessions(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('database');

        $user = $this->createUserWithRole(Role::TECHNICIEN);
        $other = $this->createUserWithRole(Role::CITOYEN);
        $current = Str::random(40);
        $this->dbSession($current, $user->id);
        $this->dbSession('ancienne-session-1', $user->id);
        $this->dbSession('ancienne-session-2', $user->id);
        $this->dbSession('session-autre-compte', $other->id);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->withCookie(config('session.cookie'), $current)
            ->post('/settings/sessions/revoke-others')
            ->assertRedirect()
            ->assertSessionHas('success', 'Toutes les autres sessions de votre compte ont été révoquées.');

        $this->assertDatabaseHas('sessions', ['id' => $current]);
        $this->assertDatabaseMissing('sessions', ['id' => 'ancienne-session-1']);
        $this->assertDatabaseMissing('sessions', ['id' => 'ancienne-session-2']);
        $this->assertDatabaseHas('sessions', ['id' => 'session-autre-compte']);
    }

    public function test_only_administrators_can_force_renewal_of_all_database_sessions(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('database');

        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $manager = $this->createUserWithRole(Role::MANAGER);
        $current = Str::random(40);
        $this->dbSession($current, $admin->id);
        $this->dbSession('session-gestionnaire', $manager->id);
        $this->dbSession('session-anonyme', null);

        $this->actingAs($manager)->withSession($this->sessionPayload($manager))
            ->post('/admin/security/renew-sessions')
            ->assertForbidden();
        $this->assertDatabaseHas('sessions', ['id' => 'session-gestionnaire']);

        $this->actingAs($admin)->withSession($this->sessionPayload($admin))
            ->withCookie(config('session.cookie'), $current)
            ->post('/admin/security/renew-sessions')
            ->assertRedirect()
            ->assertSessionHas('success', 'Les autres sessions de l’application ont été révoquées.');

        $this->assertDatabaseHas('sessions', ['id' => $current]);
        $this->assertDatabaseMissing('sessions', ['id' => 'session-gestionnaire']);
        $this->assertDatabaseMissing('sessions', ['id' => 'session-anonyme']);
    }

    // -------------------------------------------------------------------------
    // Stockage « file »
    // -------------------------------------------------------------------------

    public function test_file_sessions_are_filtered_by_owner_and_lifetime(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('file');
        $this->withoutVite();

        $user = $this->createUserWithRole(Role::TECHNICIEN);
        $other = $this->createUserWithRole(Role::CITOYEN);
        $authKey = Auth::guard()->getName();

        $this->fileSession('sessionParTableauUser', ['user' => ['id' => $user->id]]);
        $this->fileSession('sessionParCleAuth', [$authKey => $user->id]);
        $this->fileSession('sessionChiffree', ['user' => ['id' => $user->id]], encrypted: true);
        $this->fileSession('sessionExpiree', ['user' => ['id' => $user->id]], time() - (config('session.lifetime') + 10) * 60);
        $this->fileSession('sessionAutreCompte', ['user' => ['id' => $other->id]]);
        file_put_contents($this->sessionDir . '/sessionIllisible', 'contenu non sérialisé');

        config(['session.encrypt' => false]);
        $sessions = $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->get('/settings')
            ->assertOk()
            ->assertSee('Information non enregistrée')
            ->viewData('sessions');

        $ids = $sessions->pluck('id')->all();
        $this->assertContains('sessionParTableauUser', $ids);
        $this->assertContains('sessionParCleAuth', $ids);
        $this->assertNotContains('sessionExpiree', $ids);
        $this->assertNotContains('sessionAutreCompte', $ids);
        $this->assertNotContains('sessionIllisible', $ids);
        // Session chiffrée lue sans la configuration de chiffrement : ignorée.
        $this->assertNotContains('sessionChiffree', $ids);
        $this->assertSame('Appareil inconnu · Navigateur inconnu', $sessions->first()['device']);
    }

    public function test_encrypted_and_json_file_sessions_are_decoded(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('file');
        $this->withoutVite();

        $user = $this->createUserWithRole(Role::TECHNICIEN);
        config(['session.encrypt' => true, 'session.serialization' => 'json']);
        $this->fileSession('sessionJsonChiffree', ['user' => ['id' => $user->id]], encrypted: true, json: true);

        $sessions = $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->get('/settings')
            ->assertOk()
            ->viewData('sessions');

        $this->assertContains('sessionJsonChiffree', $sessions->pluck('id')->all());
    }

    public function test_user_revokes_a_file_session_they_own_only(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('file');

        $user = $this->createUserWithRole(Role::MANAGER);
        $other = $this->createUserWithRole(Role::CITOYEN);
        $mine = $this->fileSession('maSession', ['user' => ['id' => $user->id]]);
        $foreign = $this->fileSession('sessionEtrangere', ['user' => ['id' => $other->id]]);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->delete('/settings/sessions/maSession')
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertFileDoesNotExist($mine);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->delete('/settings/sessions/sessionEtrangere')
            ->assertNotFound();
        $this->assertFileExists($foreign);

        $this->actingAs($user)->withSession($this->sessionPayload($user))
            ->delete('/settings/sessions/sessionInexistante')
            ->assertNotFound();
    }

    public function test_revoke_others_and_force_renewal_with_file_sessions(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('file');

        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $current = Str::random(40);
        $this->fileSession($current, ['user' => ['id' => $admin->id]]);
        $mineOld = $this->fileSession('ancienneSessionAdmin', ['user' => ['id' => $admin->id]]);
        $citizenSession = $this->fileSession('sessionCitoyen', ['user' => ['id' => $citizen->id]]);
        $odd = $this->fileSession('nom.invalide', ['user' => ['id' => $admin->id]]);

        $this->actingAs($admin)->withSession($this->sessionPayload($admin))
            ->withCookie(config('session.cookie'), $current)
            ->post('/settings/sessions/revoke-others')
            ->assertRedirect();

        $this->assertFileDoesNotExist($mineOld);
        $this->assertFileExists($citizenSession);
        $this->assertFileExists($this->sessionDir . '/' . $current);
        // Un fichier dont le nom n'est pas un identifiant de session valide n'est jamais touché.
        $this->assertFileExists($odd);

        $this->actingAs($admin)->withSession($this->sessionPayload($admin))
            ->withCookie(config('session.cookie'), $current)
            ->post('/admin/security/renew-sessions')
            ->assertRedirect();

        $this->assertFileDoesNotExist($citizenSession);
        $this->assertFileExists($this->sessionDir . '/' . $current);
    }

    // -------------------------------------------------------------------------
    // Stockage non géré (array, cookie, …)
    // -------------------------------------------------------------------------

    public function test_unsupported_session_driver_lists_nothing_and_refuses_revocation(): void
    {
        $this->registerSessionRoutes();
        $this->useDriver('array');
        $this->withoutVite();

        $admin = $this->createUserWithRole(Role::ADMINISTRATEUR);
        $as = fn () => $this->actingAs($admin)->withSession($this->sessionPayload($admin));

        $as()->get('/settings')
            ->assertOk()
            ->assertSee('Aucune session enregistrée avec le stockage actuel.')
            ->assertDontSee('Fermer toutes les autres sessions');

        $as()->delete('/settings/sessions/' . Str::random(40))->assertNotFound();
        $as()->post('/settings/sessions/revoke-others')->assertNotFound();
        $as()->post('/admin/security/renew-sessions')->assertNotFound();
    }
}
