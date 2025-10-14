<?php
// app/Notifications/NewPreinscriptionNotification.php

namespace App\Notifications;

use App\Models\Preinscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewPreinscriptionNotification extends Notification
{
    use Queueable;

    public $preinscription;

    public function __construct(Preinscription $preinscription)
    {
        $this->preinscription = $preinscription;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📋 Nouvelle Préinscription - ' . $this->preinscription->numero_dossier)
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Une nouvelle préinscription a été soumise :')
            ->line('**Numéro de dossier :** ' . $this->preinscription->numero_dossier)
            ->line('**Nom :** ' . $this->preinscription->nom_complet)
            ->line('**Email :** ' . $this->preinscription->email)
            ->line('**Date de rendez-vous :** ' . $this->preinscription->date_rendez_vous_complete)
            ->action('Voir la préinscription', route('admin.preinscriptions.show', $this->preinscription))
            ->line('Merci de traiter cette demande dans les plus brefs délais.');
    }

    public function toArray($notifiable)
    {
        return [
            'preinscription_id' => $this->preinscription->id,
            'numero_dossier' => $this->preinscription->numero_dossier,
            'message' => 'Nouvelle préinscription de ' . $this->preinscription->nom_complet,
            'url' => route('admin.preinscriptions.show', $this->preinscription),
        ];
    }
}