<?php

namespace Tests\Unit\Incidents;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\User;
use App\Notifications\ActionCorrectiveCreated;
use App\Services\ActionCorrectiveNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class ActionCorrectiveNotificationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    private function makeModels(?User $citizen): array
    {
        $incident = (new Incident(['titre' => 'Fuite']))->forceFill(['id' => 1]);
        $incident->setRelation('user', $citizen);
        $action = (new ActionCorrective(['titre' => 'Réparer', 'statut' => 'a_faire']))->forceFill(['id' => 2]);
        $action->setRelation('technicien', null);

        return [$incident, $action];
    }

    private function citizen(?string $email = 'citoyen@example.com'): User
    {
        return (new User(['name' => 'Citoyen', 'email' => $email]))->forceFill(['id' => 7]);
    }

    public function test_it_notifies_the_citizen_and_returns_true(): void
    {
        Notification::fake();
        $citizen = $this->citizen();
        [$incident, $action] = $this->makeModels($citizen);

        $this->assertTrue((new ActionCorrectiveNotificationService())->notifyCitizen($incident, $action, true));

        Notification::assertSentTo($citizen, ActionCorrectiveCreated::class,
            fn (ActionCorrectiveCreated $n) => $n->statusChanged && $n->action === $action && $n->incident === $incident);
    }

    public function test_it_returns_false_when_incident_has_no_citizen(): void
    {
        Notification::fake();
        [$incident, $action] = $this->makeModels(null);

        $this->assertFalse((new ActionCorrectiveNotificationService())->notifyCitizen($incident, $action));

        Notification::assertNothingSent();
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_it_returns_false_when_citizen_has_no_email(): void
    {
        Notification::fake();
        [$incident, $action] = $this->makeModels($this->citizen(''));

        $this->assertFalse((new ActionCorrectiveNotificationService())->notifyCitizen($incident, $action));

        Notification::assertNothingSent();
    }

    public function test_it_returns_false_and_logs_when_sending_fails(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP down'));
        [$incident, $action] = $this->makeModels($this->citizen());

        $this->assertFalse((new ActionCorrectiveNotificationService())->notifyCitizen($incident, $action));

        Log::shouldHaveReceived('error')->once();
    }
}
