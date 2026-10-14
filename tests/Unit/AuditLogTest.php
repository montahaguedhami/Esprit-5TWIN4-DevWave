<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_stores_action_resource_and_details(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = AuditLog::record('user_login', 'User', $user->id, ['source' => 'web'], $user);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'user_login',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
        $this->assertSame(['source' => 'web'], $log->details);
        $this->assertTrue($log->user->is($user));
    }

    public function test_record_falls_back_to_session_email_when_no_authenticated_user(): void
    {
        $this->withSession(['user' => ['email' => 'session@aquasecure.tn']]);

        $log = AuditLog::record('guest_action');

        $this->assertSame('guest_action', $log->action);
        $this->assertSame('session@aquasecure.tn', $log->user_email);
        $this->assertNull($log->user_id);
        $this->assertNull($log->resource_type);
    }
}
