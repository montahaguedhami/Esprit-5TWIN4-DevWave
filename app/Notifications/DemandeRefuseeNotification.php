<?php

namespace App\Notifications;

use App\Models\DemandeFinancement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le citoyen que sa demande de financement a été refusée
 * par le manager, avec le motif de refus.
 */
class DemandeRefuseeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly DemandeFinancement $demande,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $projet = $this->demande->projet;
        $prenom = explode(' ', trim((string) $notifiable->name))[0];

        $message = (new MailMessage)
            ->subject('ℹ️ Votre demande de financement — AquaSecure')
            ->greeting('Bonjour ' . $prenom . ',')
            ->line('Votre demande de financement pour le projet suivant a été **refusée** par le gestionnaire :')
            ->line('**' . ($projet->nom ?? 'Projet AquaSecure') . '**')
            ->line('Montant demandé : **' . number_format((float) $this->demande->montant, 2, ',', ' ') . ' TND**');

        if (! empty($this->demande->motif_refus)) {
            $message->line('**Motif du refus :** ' . $this->demande->motif_refus);
        }

        return $message
            ->line('Vous pouvez consulter le détail de votre demande dans votre espace citoyen.')
            ->action('Voir ma demande', url('/citizen/demandes/' . $this->demande->id))
            ->line('Si vous avez des questions, n\'hésitez pas à contacter l\'administration.')
            ->salutation('L\'équipe AquaSecure 💧');
    }
}
