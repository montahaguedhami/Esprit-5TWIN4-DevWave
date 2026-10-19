<?php

namespace App\Notifications;

use App\Models\DemandeFinancement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le citoyen que sa demande de financement a été acceptée
 * par le manager. Il peut maintenant procéder à la contribution simulée.
 */
class DemandeAccepteeNotification extends Notification
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

        return (new MailMessage)
            ->subject('✅ Votre demande de financement a été acceptée — AquaSecure')
            ->greeting('Bonjour ' . $prenom . ',')
            ->line('Bonne nouvelle ! Votre demande de financement pour le projet suivant a été **acceptée** par le gestionnaire :')
            ->line('**' . ($projet->nom ?? 'Projet AquaSecure') . '**')
            ->line('Montant demandé : **' . number_format((float) $this->demande->montant, 2, ',', ' ') . ' TND**')
            ->line('Vous pouvez maintenant procéder à votre contribution simulée en accédant à votre espace citoyen.')
            ->action('Accéder à ma demande', url('/citizen/demandes/' . $this->demande->id))
            ->line('Cette invitation est valable tant que le projet reste ouvert au financement.')
            ->line('---')
            ->line('*Rappel : cette opération est une simulation locale. Aucun paiement réel ne sera effectué.*')
            ->salutation('L\'équipe AquaSecure 💧');
    }
}
