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
        public ActionCorrective $action
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'action_corrective',
            'incident_id' => $this->incident->id,
            'incident_title' => $this->incident->titre,
            'action_id' => $this->action->id,
            'action_title' => $this->action->titre,
            'action_description' => $this->action->description,
            'message' => 'Le gestionnaire a ajouté l’action corrective « ' . $this->action->titre . ' » à votre incident. Consultez les détails ci-dessous.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle action corrective pour votre incident')
            ->greeting('Bonjour ' . ($notifiable->name ?? ''))
            ->line('Une nouvelle action corrective a été ajoutée à votre incident : ' . $this->incident->titre . '.')
            ->line('Action : ' . $this->action->titre)
            ->line('Description : ' . $this->action->description)
            ->line('Statut : ' . $this->action->statut)
            ->action('Voir mon incident', url(route('incidents.show', $this->incident)))
            ->line('Consultez votre espace AquaSecure pour suivre l’évolution de votre signalement.');
    }
}
