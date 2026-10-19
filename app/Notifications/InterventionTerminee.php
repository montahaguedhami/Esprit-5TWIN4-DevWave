<?php

namespace App\Notifications;

use App\Models\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Prévient les gestionnaires qu'une intervention est terminée.
 */
class InterventionTerminee extends Notification
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

        $mail = (new MailMessage)
            ->subject('Intervention #'.$intervention->id.' terminée')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->phrase())
            ->line('Coût : '.number_format((float) $intervention->cout, 2, ',', ' ').' DT');

        if ($intervention->estPreventive() && $intervention->infrastructure?->date_prochaine_maintenance) {
            $mail->line('Prochaine maintenance préventive : '.$intervention->infrastructure->date_prochaine_maintenance->format('d/m/Y').'.');
        }

        return $mail
            ->action('Voir l\'intervention', route('manager.interventions.show', $intervention->id))
            ->salutation('L\'équipe AquaSecure');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => 'Intervention #'.$this->intervention->id.' terminée',
            'message' => $this->phrase(),
            'icone' => 'check-circle-2',
            'couleur' => 'emerald',
            'url' => route('manager.interventions.show', $this->intervention->id),
            'intervention_id' => $this->intervention->id,
        ];
    }

    private function phrase(): string
    {
        $intervention = $this->intervention;

        return ($intervention->technicien?->nom ?? 'Un technicien').' a terminé l\'intervention '
            .mb_strtolower($intervention->type).' du '.$intervention->date->format('d/m/Y')
            .' ('.($intervention->infrastructure?->nom ?? 'hors infrastructure').').';
    }
}
