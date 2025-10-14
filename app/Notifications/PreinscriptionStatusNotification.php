<?php
// app/Notifications/PreinscriptionStatusNotification.php

namespace App\Notifications;

use App\Models\Preinscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PreinscriptionStatusNotification extends Notification
{
    use Queueable;

    public $preinscription;
    public $ancienStatut;
    public $nouveauStatut;

    public function __construct(Preinscription $preinscription, $ancienStatut, $nouveauStatut)
    {
        $this->preinscription = $preinscription;
        $this->ancienStatut = $ancienStatut;
        $this->nouveauStatut = $nouveauStatut;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $statutLabels = [
            'valide' => 'validée',
            'rejete' => 'rejetée', 
            'reclasse' => 'reclassée',
            'en_attente' => 'mise en attente'
        ];

        $subject = '📊 Mise à jour de votre préinscription - ' . $this->preinscription->numero_dossier;

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . $this->preinscription->prenom . '!')
            ->line('Votre préinscription a été ' . $statutLabels[$this->nouveauStatut] . '.');

        if ($this->preinscription->commentaire_agent) {
            $message->line('**Commentaire :** ' . $this->preinscription->commentaire_agent);
        }

        if ($this->nouveauStatut === 'valide') {
            $message->line('🎉 Félicitations! Votre dossier a été approuvé.')
                   ->line('Vous recevrez prochainement les instructions pour la suite du processus.');
        } elseif ($this->nouveauStatut === 'rejete') {
            $message->line('Pour toute question, n\'hésitez pas à nous contacter.');
        }

        return $message->action('Voir votre dossier', url('/preinscription/confirmation/' . $this->preinscription->id))
                      ->line('Merci de votre confiance!');
    }
}