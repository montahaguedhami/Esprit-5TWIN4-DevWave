<?php

namespace App\Notifications;

use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends the 6-digit OTP required to recover an AquaSecure account.
 *
 * The OTP itself is passed in clear text only for the duration of the
 * notification; it is never logged nor persisted by this class.
 */
class SendPasswordResetOtp extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $otp,
        public readonly PasswordResetOtp $request,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        $minutes = PasswordResetOtp::OTP_TTL_MINUTES;

        return (new MailMessage)
            ->subject('AquaSecure — Votre code de réinitialisation')
            ->greeting('Bonjour '.($notifiable->name ?: ' cher utilisateur').',')
            ->line('Vous avez demandé la réinitialisation de votre mot de passe AquaSecure.')
            ->line('Voici votre code de vérification :')
            ->line('**'.$this->otp.'**') // 6 chiffres, lisible immédiatement
            ->line('Ce code est valable pendant '.$minutes.' minutes.')
            ->line('Si vous avez dépassé le nombre d\'essais autorisés, demandez un nouveau code.')
            ->action('Je ne reconnais pas cette demande', url('/forgot-password'))
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, ignorez simplement cet email : votre mot de passe restera inchangé.')
            ->line('Ne communiquez jamais ce code à quelqu\'un d\'autre, y compris en se présentant comme AquaSecure.')
            ->salutation('L\'équipe AquaSecure');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'expires_at' => $this->request->otp_expires_at?->toIso8601String(),
        ];
    }
}
