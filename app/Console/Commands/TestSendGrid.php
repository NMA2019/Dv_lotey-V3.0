<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Preinscription;
use App\Services\NotificationService;

class TestSendGrid extends Command
{
    protected $signature = 'sendgrid:test {email}';
    protected $description = 'Tester SendGrid avec une notification';

    public function handle(NotificationService $notificationService)
    {
        $email = $this->argument('email');
        
        $this->info("🧪 Test SendGrid vers: {$email}");
        
        // Créer une préinscription fictive
        $preinscription = new Preinscription([
            'numero_dossier' => 'TEST-' . time(),
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => $email,
            'date_rendez_vous' => now()->addDays(7),
            'heure_rendez_vous' => '10:00',
            'commentaire_agent' => 'Ceci est un test SendGrid',
            'created_at' => now()
        ]);

        $this->info("📧 Envoi de la notification de confirmation...");
        
        if ($notificationService->sendPreinscriptionConfirmation($preinscription)) {
            $this->info("✅ Email envoyé avec succès via SendGrid!");
        } else {
            $this->error("❌ Échec de l'envoi via SendGrid");
        }

        $this->info("📧 Envoi de la notification de validation...");
        
        if ($notificationService->sendValidationNotification($preinscription)) {
            $this->info("✅ Email de validation envoyé avec succès!");
        } else {
            $this->error("❌ Échec de l'envoi de validation");
        }
    }
}