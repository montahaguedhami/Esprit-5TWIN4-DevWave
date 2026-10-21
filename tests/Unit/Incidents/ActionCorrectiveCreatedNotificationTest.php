<?php

namespace Tests\Unit\Incidents;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\Technicien;
use App\Models\User;
use App\Notifications\ActionCorrectiveCreated;
use Tests\TestCase;

class ActionCorrectiveCreatedNotificationTest extends TestCase
{
    private function makeModels(string $statut = 'a_faire', ?string $technicien = 'Sami'): array
    {
        $incident = (new Incident(['titre' => 'Fuite rue de Rome']))->forceFill(['id' => 12]);
        $action = (new ActionCorrective([
            'titre' => 'Fermer la vanne',
            'description' => 'Isoler la conduite.',
            'statut' => $statut,
        ]))->forceFill(['id' => 34]);
        $action->setRelation('technicien', $technicien ? new Technicien(['nom' => $technicien]) : null);

        return [$incident, $action, new User(['name' => 'Amira', 'email' => 'amira@example.com'])];
    }

    public function test_it_is_sent_by_database_and_mail(): void
    {
        [$incident, $action, $user] = $this->makeModels();

        $this->assertSame(['database', 'mail'], (new ActionCorrectiveCreated($incident, $action))->via($user));
    }

    public function test_database_payload_for_a_new_action(): void
    {
        [$incident, $action, $user] = $this->makeModels();

        $data = (new ActionCorrectiveCreated($incident, $action))->toDatabase($user);

        $this->assertSame('action_corrective', $data['type']);
        $this->assertSame(12, $data['incident_id']);
        $this->assertSame('Fuite rue de Rome', $data['incident_title']);
        $this->assertSame(34, $data['action_id']);
        $this->assertSame('Fermer la vanne', $data['action_title']);
        $this->assertSame('a_faire', $data['action_status']);
        $this->assertStringContainsString('Le gestionnaire a ajouté', $data['message']);
    }

    public function test_database_payload_for_a_status_change_mentions_technician_and_label(): void
    {
        [$incident, $action, $user] = $this->makeModels('en_cours');

        $data = (new ActionCorrectiveCreated($incident, $action, true))->toDatabase($user);

        $this->assertSame('action_corrective_status', $data['type']);
        $this->assertStringContainsString('Sami', $data['message']);
        $this->assertStringContainsString('En cours', $data['message']);
    }

    public function test_status_change_without_technician_uses_fallback_name(): void
    {
        [$incident, $action, $user] = $this->makeModels('terminee', null);

        $data = (new ActionCorrectiveCreated($incident, $action, true))->toDatabase($user);

        $this->assertStringContainsString('technicien affecté', $data['message']);
        $this->assertStringContainsString('Terminée', $data['message']);
    }

    public function test_mail_for_new_action(): void
    {
        [$incident, $action, $user] = $this->makeModels();

        $mail = (new ActionCorrectiveCreated($incident, $action))->toMail($user);

        $this->assertSame('Nouvelle action corrective pour votre incident', $mail->subject);
        $this->assertSame('Bonjour Amira', $mail->greeting);
        $this->assertContains('Action : Fermer la vanne', $mail->introLines);
        $this->assertContains('Nouveau statut : À faire', $mail->introLines);
        $this->assertSame(route('incidents.show', 12), $mail->actionUrl);
    }

    public function test_mail_for_status_change(): void
    {
        [$incident, $action, $user] = $this->makeModels('terminee');

        $mail = (new ActionCorrectiveCreated($incident, $action, true))->toMail($user);

        $this->assertSame('Mise à jour d’une action corrective', $mail->subject);
        $this->assertContains('Technicien responsable : Sami', $mail->introLines);
        $this->assertContains('Nouveau statut : Terminée', $mail->introLines);
    }
}
