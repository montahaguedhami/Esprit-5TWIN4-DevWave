<?php

namespace App\Notifications;

use App\Models\Intervention;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Prévient le technicien qu'une de ses interventions a été replanifiée ou annulée.
 */
class InterventionModifiee extends Notification
{
    use Queueable;

    public function __construct(public Intervention $intervention, public ?Carbon $ancienneDate = null)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function estAnnulee(): bool
    {
        return $this->intervention->statut === 'Annulée';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->titre())
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->phrase())
            ->line('Description : '.$this->intervention->description);

        if (! $this->estAnnulee()) {
            $mail->action('Voir l\'intervention', route('technician.interventions.show', $this->intervention->id));
        }

        return $mail->salutation('L\'équipe AquaSecure');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => $this->titre(),
            'message' => $this->phrase(),
            'icone' => $this->estAnnulee() ? 'calendar-x' : 'calendar-range',
            'couleur' => $this->estAnnulee() ? 'rose' : 'amber',
            'url' => $this->estAnnulee() ? null : route('technician.interventions.show', $this->intervention->id),
            'intervention_id' => $this->intervention->id,
        ];
    }

    private function titre(): string
    {
        return $this->estAnnulee()
            ? 'Intervention #'.$this->intervention->id.' annulée'
            : 'Intervention #'.$this->intervention->id.' replanifiée';
    }

    private function phrase(): string
    {
        $infrastructure = $this->intervention->infrastructure?->nom ?? 'hors infrastructure';

        if ($this->estAnnulee()) {
            return 'L\'intervention du '.$this->intervention->date->format('d/m/Y').' ('.$infrastructure.') a été annulée.';
        }

        return 'L\'intervention ('.$infrastructure.') est déplacée'
            .($this->ancienneDate ? ' du '.$this->ancienneDate->format('d/m/Y') : '')
            .' au '.$this->intervention->date->format('d/m/Y').'.';
    }
}
