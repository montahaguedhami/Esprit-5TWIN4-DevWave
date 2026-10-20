<?php

namespace App\Services;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Notifications\ActionCorrectiveCreated;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActionCorrectiveNotificationService
{
    public function notifyCitizen(Incident $incident, ActionCorrective $action, bool $statusChanged = false): bool
    {
        $incident->loadMissing('user');
        $action->loadMissing('technicien');

        $citizen = $incident->user;
        if (! $citizen || empty($citizen->email)) {
            Log::warning('Impossible d’envoyer la notification : citoyen ou email introuvable.', [
                'incident_id' => $incident->id,
                'action_id' => $action->id,
                'citizen_id' => $citizen?->id,
            ]);
            return false;
        }

        try {
            $citizen->notify(new ActionCorrectiveCreated($incident, $action, $statusChanged));
            Log::info('Notification d’action corrective envoyée avec succès au citoyen.', [
                'incident_id' => $incident->id,
                'action_id' => $action->id,
                'citizen_id' => $citizen->id,
                'citizen_email' => $citizen->email,
            ]);
            return true;
        } catch (Throwable $exception) {
            Log::error('Impossible d’envoyer la notification d’action corrective au citoyen.', [
                'incident_id' => $incident->id,
                'action_id' => $action->id,
                'citizen_id' => $citizen->id,
                'citizen_email' => $citizen->email,
                'exception' => $exception->getMessage(),
            ]);
            return false;
        }
    }
}
