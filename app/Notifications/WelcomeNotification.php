<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue sur AquaSecure 💧')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous sommes ravis de vous accueillir sur **AquaSecure** !')
            ->line('Votre compte a été créé avec succès. Vous pouvez maintenant :')
            ->line('📍 Consulter les projets d\'infrastructure de votre région')
            ->line('🚨 Signaler des incidents et fuites du réseau')
            ->line('📊 Suivre l\'avancement des travaux en cours')
            ->action('Accéder à mon espace', url('/login'))
            ->line('Si vous avez des questions, contactez-nous à : support@aquasecure.tn')
            ->salutation('L\'équipe AquaSecure 💧');
    }
}
