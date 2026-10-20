<?php

namespace App\Notifications;

use App\Models\ActionCorrective;
use App\Models\Incident;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActionCorrectiveCreated extends Notification
{
    public function __construct(
        public Incident $incident,
        public ActionCorrective $action,
        public bool $statusChanged = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = $this->statusChanged
            ? 'Le technicien ' . ($this->action->technicien?->nom ?? 'affecté') . ' a changé le statut de l’action « ' . $this->action->titre . ' » en « ' . $this->statusLabel() . ' ».'
            : 'Le gestionnaire a ajouté l’action corrective « ' . $this->action->titre . ' » à votre incident. Consultez les détails ci-dessous.';

        return [
            'type' => $this->statusChanged ? 'action_corrective_status' : 'action_corrective',
            'incident_id' => $this->incident->id,
            'incident_title' => $this->incident->titre,
            'action_id' => $this->action->id,
            'action_title' => $this->action->titre,
            'action_description' => $this->action->description,
            'action_status' => $this->action->statut,
            'message' => $message,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->statusChanged ? 'Mise à jour d’une action corrective' : 'Nouvelle action corrective pour votre incident')
            ->greeting('Bonjour ' . ($notifiable->name ?? ''));

        if ($this->statusChanged) {
            $message->line('Le technicien ' . ($this->action->technicien?->nom ?? 'affecté') . ' a mis à jour le statut d’une action pour votre incident : ' . $this->incident->titre . '.');
        } else {
            $message->line('Le gestionnaire a ajouté une action corrective à votre incident : ' . $this->incident->titre . '.');
        }

        return $message
            ->line('Action : ' . $this->action->titre)
            ->line('Description : ' . $this->action->description)
            ->line('Technicien responsable : ' . ($this->action->technicien?->nom ?? 'À confirmer'))
            ->line('Nouveau statut : ' . $this->statusLabel())
            ->line('Vous pouvez consulter les détails et suivre cette action dans votre compte sur le site AquaSecure.')
            ->action('Consulter mon incident', url(route('incidents.show', $this->incident)));
    }

    private function statusLabel(): string
    {
        return match ($this->action->statut) {
            'a_faire' => 'À faire',
            'en_cours' => 'En cours',
            'terminee' => 'Terminée',
            default => ucfirst(str_replace('_', ' ', (string) $this->action->statut)),
        };
    }
}
