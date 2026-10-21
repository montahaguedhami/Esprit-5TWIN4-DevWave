<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_sent_by_mail_verifies_the_address(): void
    {
        $user = User::factory()->unverified()->create();

        $lien = (new VerifyEmail)->toMail($user)->actionUrl;

        $this->assertStringContainsString(hash('sha256', $user->email), $lien);
        $this->actingAs($user)->get($lien)->assertRedirect(route('profile.show'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_signed_link_with_a_wrong_fingerprint_is_refused(): void
    {
        $user = User::factory()->unverified()->create();

        // Empreinte d'une autre adresse : refusée même dans une URL correctement signée
        $lien = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => hash('sha256', 'autre.adresse@aquasecure.tn'),
        ]);

        $this->actingAs($user)->get($lien)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
