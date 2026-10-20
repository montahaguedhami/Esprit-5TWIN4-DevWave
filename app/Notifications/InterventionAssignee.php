<?php

namespace App\Notifications;

use App\Models\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Prévient le technicien qu'une intervention vient de lui être affectée.
 */
class InterventionAssignee extends Notification
{
    use Queueable;

    public function __construct(public Intervention $intervention)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $intervention = $this->intervention;

        return (new MailMessage)
            ->subject('Nouvelle intervention '.mb_strtolower($intervention->type).' le '.$intervention->date->format('d/m/Y'))
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Une intervention vous a été affectée pour le **'.$intervention->date->translatedFormat('l d F Y').'**.')
            ->line('Infrastructure : '.($intervention->infrastructure?->nom ?? 'non rattachée'))
            ->line('Description : '.$intervention->description)
            ->action('Voir l\'intervention', route('technician.interventions.show', $intervention->id))
            ->salutation('L\'équipe AquaSecure');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $intervention = $this->intervention;

        return [
            'titre' => 'Nouvelle intervention '.mb_strtolower($intervention->type),
            'message' => $intervention->date->format('d/m/Y').' · '.($intervention->infrastructure?->nom ?? 'Hors infrastructure').' — '.str($intervention->description)->limit(80),
            'icone' => $intervention->estPreventive() ? 'calendar-clock' : 'wrench',
            'couleur' => 'cyan',
            'url' => route('technician.interventions.show', $intervention->id),
            'intervention_id' => $intervention->id,
        ];
    }
}
