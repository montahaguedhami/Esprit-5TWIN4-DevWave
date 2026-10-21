<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le citoyen après l'enregistrement réussi d'une contribution simulée.
 *
 * Cette notification n'est envoyée QUE lorsque la simulation a réussi
 * (statut CONFIRME). Elle ne doit jamais être envoyée pour un paiement
 * échoué, annulé ou en attente.
 *
 * Mentions légales obligatoires : la mention "simulation locale" apparaît
 * explicitement dans le corps et dans l'objet de l'e-mail.
 */
class PaiementConfirmeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Paiement $paiement,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $demande = $this->paiement->demande;
        $projet  = $demande?->projet;
        $prenom  = explode(' ', trim((string) $notifiable->name))[0];

        return (new MailMessage)
            ->subject('💧 Merci pour votre contribution au projet ' . ($projet->nom ?? 'AquaSecure') . ' — Simulation')
            ->greeting('Bonjour ' . $prenom . ',')
            ->line('Nous vous remercions sincèrement pour votre participation au financement participatif du projet AquaSecure.')
            ->line('Votre contribution simulée a été **enregistrée avec succès** dans notre système.')
            ->line('---')
            ->line('**Récapitulatif de votre contribution**')
            ->line('📁 Projet : **' . ($projet->nom ?? '—') . '**')
            ->line('💰 Montant : **' . number_format((float) $this->paiement->montant, 2, ',', ' ') . ' TND**')
            ->line('🔖 Référence : **' . $this->paiement->reference_interne . '**')
            ->line('📅 Date : **' . ($this->paiement->confirmed_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i')) . '**')
            ->line('✅ Statut : **Contribution simulée enregistrée avec succès**')
            ->line('---')
            ->line('Grâce à votre engagement, vous contribuez à l\'amélioration du réseau d\'eau potable dans votre région.')
            ->action('Consulter mon reçu', url('/citizen/paiements/' . $this->paiement->id . '/recu'))
            ->line('---')
            ->line('> ⚠️ **Avis important :** cette opération a été réalisée dans le cadre d\'une **simulation locale**. Aucun paiement ni transfert d\'argent réel n\'a été effectué. Ce message est uniquement un reçu de simulation académique.')
            ->salutation('L\'équipe AquaSecure 💧');
    }
}
