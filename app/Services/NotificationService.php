<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;
use App\Models\Preinscription;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class NotificationService
{
    protected $sendGrid;

    public function __construct()
    {
        $this->sendGrid = new SendGrid(env('SENDGRID_API_KEY'));
    }

    /**
     * Envoyer une notification de confirmation de préinscription
     */
    public function sendPreinscriptionConfirmation(Preinscription $preinscription)
    {
        $subject = "Confirmation de votre préinscription - " . config('app.name');
        
        $content = $this->generatePreinscriptionConfirmationEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de validation
     */
    public function sendValidationNotification(Preinscription $preinscription)
    {
        $subject = "✅ Votre préinscription a été validée - " . config('app.name');
        
        $content = $this->generateValidationEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de rejet
     */
    public function sendRejectionNotification(Preinscription $preinscription)
    {
        $subject = "❌ Statut de votre préinscription - " . config('app.name');
        
        $content = $this->generateRejectionEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de reclassement
     */
    public function sendReclassificationNotification(Preinscription $preinscription)
    {
        $subject = "🔄 Mise à jour de votre préinscription - " . config('app.name');
        
        $content = $this->generateReclassificationEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de confirmation de paiement
     */
    public function sendPaiementConfirmationNotification(Preinscription $preinscription)
    {
        $subject = "✅ Paiement confirmé - " . config('app.name');
        
        $content = $this->generatePaiementConfirmationEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de rejet de paiement
     */
    public function sendPaiementRejectionNotification(Preinscription $preinscription, string $raison)
    {
        $subject = "❌ Problème avec votre paiement - " . config('app.name');
        
        $content = $this->generatePaiementRejectionEmail($preinscription, $raison);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de rappel de rendez-vous
     */
    public function sendRappelRendezVous(Preinscription $preinscription)
    {
        $subject = "📅 Rappel de votre rendez-vous - " . config('app.name');
        
        $content = $this->generateRappelRendezVousEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Envoyer une notification de statut de paiement en attente
     */
    public function sendPaiementEnAttenteNotification(Preinscription $preinscription)
    {
        $subject = "⏳ Paiement en attente - " . config('app.name');
        
        $content = $this->generatePaiementEnAttenteEmail($preinscription);

        return $this->sendEmail($preinscription->email, $subject, $content);
    }

    /**
     * Notifier les administrateurs d'une nouvelle préinscription
     */
    public function notifyAdminsNewPreinscription(Preinscription $preinscription)
    {
        $subject = "🆕 Nouvelle préinscription - " . $preinscription->numero_dossier;
        
        $content = $this->generateAdminNotificationEmail($preinscription);

        // Récupérer les emails des administrateurs (à adapter selon votre modèle User)
        $adminEmails = ['admin@votredomaine.com']; // Exemple
        
        $results = [];
        foreach ($adminEmails as $email) {
            $results[$email] = $this->sendEmail($email, $subject, $content);
        }

        return $results;
    }

    /**
     * Notifier les administrateurs d'un paiement validé
     */
    public function notifyAdminsPaiementValide(Paiement $paiement)
    {
        $subject = "💰 Paiement validé - " . $paiement->preinscription->numero_dossier;
        
        $content = $this->generateAdminPaiementValideEmail($paiement);

        $adminEmails = ['admin@votredomaine.com']; // Exemple
        
        $results = [];
        foreach ($adminEmails as $email) {
            $results[$email] = $this->sendEmail($email, $subject, $content);
        }

        return $results;
    }

    /**
     * Générer le PDF du reçu de préinscription
     */
    public function generateReceiptPdf(Preinscription $preinscription)
    {
        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement,
            'date_emission' => now()->format('d/m/Y à H:i'),
            'entreprise' => [
                'nom' => config('app.name'),
                'adresse' => '123 Avenue de la Formation, Douala',
                'telephone' => '+237 XXX XXX XXX',
                'email' => 'contact@votredomaine.com'
            ]
        ];

        try {
            $pdf = Pdf::loadView('pdf.preinscription-receipt', $data);
            return $pdf->download('recu-' . $preinscription->numero_dossier . '.pdf');
        } catch (\Exception $e) {
            // Fallback si la vue n'existe pas
            return $this->generateDefaultReceiptPdf($preinscription, $data);
        }
    }

    /**
     * Générer le PDF de détail du dossier
     */
    public function generateDetailPdf(Preinscription $preinscription)
    {
        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement,
            'agent' => $preinscription->agent,
            'date_export' => now()->format('d/m/Y à H:i')
        ];

        try {
            $pdf = Pdf::loadView('pdf.preinscription-detail', $data);
            return $pdf->download('dossier-' . $preinscription->numero_dossier . '.pdf');
        } catch (\Exception $e) {
            return $this->generateDefaultDetailPdf($preinscription, $data);
        }
    }

    /**
     * Méthode générique pour envoyer des emails via SendGrid
     */
    private function sendEmail($to, $subject, $content)
    {
        try {
            $email = new Mail();
            $email->setFrom(env('MAIL_FROM_ADDRESS', 'noreply@votredomaine.com'), env('MAIL_FROM_NAME', config('app.name')));
            $email->setSubject($subject);
            $email->addTo($to);
            $email->addContent("text/html", $content);

            // Ajouter le suivi
            $email->setTrackingSettings([
                'click_tracking' => ['enable' => true, 'enable_text' => true],
                'open_tracking' => ['enable' => true]
            ]);

            $response = $this->sendGrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                Log::info("Email envoyé avec succès", [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->statusCode()
                ]);
                return true;
            } else {
                Log::error("Erreur d'envoi d'email", [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->statusCode(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'envoi d'email: " . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject
            ]);
            return false;
        }
    }

    /**
     * Générer le contenu email de confirmation de préinscription
     */
    private function generatePreinscriptionConfirmationEmail(Preinscription $preinscription)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Confirmation de Préinscription</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .dossier-info { background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff; }
                    .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                    .info-row { display: flex; margin-bottom: 8px; }
                    .info-label { font-weight: bold; width: 150px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎉 Confirmation de Préinscription</h1>
                        <p>Votre demande a été enregistrée avec succès</p>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <p>Nous accusons réception de votre préinscription. Votre dossier a été créé et est maintenant en attente de traitement.</p>
                        
                        <div class='dossier-info'>
                            <h3 style='margin-top: 0; color: #0056b3;'>📋 Détails de votre dossier</h3>
                            <div class='info-row'><span class='info-label'>Numéro de dossier:</span> <strong>{$preinscription->numero_dossier}</strong></div>
                            <div class='info-row'><span class='info-label'>Date de création:</span> {$preinscription->created_at->format('d/m/Y à H:i')}</div>
                            <div class='info-row'><span class='info-label'>Statut:</span> <span style='color: #ffc107; font-weight: bold;'>⏳ En attente</span></div>
                        </div>

                        <h4>📅 Rendez-vous programmé</h4>
                        <div class='info-row'><span class='info-label'>Date:</span> {$preinscription->date_rendez_vous->format('d/m/Y')}</div>
                        <div class='info-row'><span class='info-label'>Heure:</span> {$preinscription->heure_rendez_vous}</div>
                        <div class='info-row'><span class='info-label'>Lieu:</span> Centre de Formation Professionnelle, Douala</div>

                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                            <h4 style='margin-top: 0; color: #856404;'>💡 Prochaines étapes</h4>
                            <ol style='margin-bottom: 0;'>
                                <li>Validation de votre dossier par notre équipe</li>
                                <li>Paiement des frais de préinscription (5 000 FCFA)</li>
                                <li>Présentation au rendez-vous avec les documents originaux</li>
                            </ol>
                        </div>

                        <p>Vous pouvez suivre l'état de votre préinscription à tout moment en utilisant votre numéro de dossier sur notre plateforme.</p>
                        
                        <a href='" . route('preinscription.confirmation', $preinscription) . "' class='btn'>📁 Voir mon dossier</a>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>Pour toute question, contactez-nous à <a href='mailto:contact@votredomaine.com'>contact@votredomaine.com</a></p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de validation
     */
    private function generateValidationEmail(Preinscription $preinscription)
    {
        $commentaire = $preinscription->commentaire_agent ? "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'><strong>💬 Commentaire:</strong> {$preinscription->commentaire_agent}</div>" : "";

        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Préinscription Validée</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .success-box { background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                    .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>✅ Préinscription Validée</h1>
                        <p>Félicitations ! Votre dossier a été approuvé</p>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <div class='success-box'>
                            <h3 style='margin-top: 0; color: #155724;'>🎉 Validation Confirmée</h3>
                            <p style='margin-bottom: 0;'>Votre dossier <strong>{$preinscription->numero_dossier}</strong> a été validé avec succès par notre équipe.</p>
                        </div>

                        {$commentaire}

                        <h4>📋 Prochaines étapes à suivre</h4>
                        <ul>
                            <li><strong>Effectuer le paiement</strong> des frais de préinscription (5 000 FCFA)</li>
                            <li><strong>Présentez-vous au rendez-vous</strong> avec les documents originaux</li>
                            <li><strong>Arrivez 15 minutes avant</strong> l'heure prévue</li>
                        </ul>

                        <h4>📅 Rendez-vous confirmé</h4>
                        <div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>
                            <strong>Date:</strong> {$preinscription->date_rendez_vous->format('d/m/Y')}<br>
                            <strong>Heure:</strong> {$preinscription->heure_rendez_vous}<br>
                            <strong>Lieu:</strong> Centre de Formation Professionnelle, Douala
                        </div>

                        <div style='margin: 20px 0;'>
                            <a href='" . route('payment.form', $preinscription) . "' class='btn'>💳 Procéder au paiement</a>
                        </div>

                        <p style='color: #666;'>Si vous avez déjà effectué le paiement, merci de l'ignorer.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de rejet
     */
    private function generateRejectionEmail(Preinscription $preinscription)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Statut de Préinscription</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .info-box { background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>❌ Décision concernant votre préinscription</h1>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <div class='info-box'>
                            <h3 style='margin-top: 0; color: #721c24;'>Décision concernant votre dossier</h3>
                            <p style='margin-bottom: 0;'>Votre dossier <strong>{$preinscription->numero_dossier}</strong> n'a pas pu être accepté.</p>
                        </div>

                        " . ($preinscription->commentaire_agent ? "
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>📝 Motif :</strong><br>
                            {$preinscription->commentaire_agent}
                        </div>
                        " : "") . "

                        <p>Nous vous remercions pour l'intérêt que vous portez à notre établissement.</p>
                        
                        <p>Pour toute information complémentaire ou pour soumettre une nouvelle demande, n'hésitez pas à nous contacter.</p>

                        <div style='text-align: center; margin: 25px 0;'>
                            <p><strong>Service des admissions</strong><br>
                            📞 +237 XXX XXX XXX<br>
                            📧 admissions@votredomaine.com</p>
                        </div>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de reclassement
     */
    private function generateReclassificationEmail(Preinscription $preinscription)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Mise à jour de Préinscription</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .info-box { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🔄 Mise à jour de votre préinscription</h1>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <div class='info-box'>
                            <h3 style='margin-top: 0; color: #856404;'>Reclassement de votre dossier</h3>
                            <p style='margin-bottom: 0;'>Votre dossier <strong>{$preinscription->numero_dossier}</strong> a été reclassé.</p>
                        </div>

                        " . ($preinscription->commentaire_agent ? "
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>📝 Informations :</strong><br>
                            {$preinscription->commentaire_agent}
                        </div>
                        " : "") . "

                        <p>Notre équipe vous contactera prochainement pour les prochaines étapes et les modalités de poursuite de votre dossier.</p>

                        <p>Nous restons à votre disposition pour toute information complémentaire.</p>

                        <div style='text-align: center; margin: 25px 0;'>
                            <p><strong>Service des admissions</strong><br>
                            📞 +237 XXX XXX XXX<br>
                            📧 admissions@votredomaine.com</p>
                        </div>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de confirmation de paiement
     */
    private function generatePaiementConfirmationEmail(Preinscription $preinscription)
    {
        $paiement = $preinscription->paiement;

        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Paiement Confirmé</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .success-box { background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8; }
                    .btn { display: inline-block; padding: 12px 24px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>✅ Paiement Confirmé</h1>
                        <p>Votre transaction a été validée avec succès</p>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <div class='success-box'>
                            <h3 style='margin-top: 0; color: #0c5460;'>💳 Paiement Validé</h3>
                            <p style='margin-bottom: 0;'>Votre paiement pour la préinscription <strong>{$preinscription->numero_dossier}</strong> a été confirmé.</p>
                        </div>

                        <h4>📊 Détails de la transaction</h4>
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
                            <strong>Montant payé :</strong> " . number_format($paiement->montant, 0, ',', ' ') . " FCFA<br>
                            <strong>Mode de paiement :</strong> " . strtoupper($paiement->mode_paiement) . "<br>
                            <strong>Référence :</strong> {$paiement->reference_paiement}<br>
                            <strong>Date du paiement :</strong> " . ($paiement->date_paiement ? $paiement->date_paiement->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i')) . "
                        </div>

                        <p>Votre dossier est maintenant complet et en attente de validation finale par notre équipe.</p>

                        <h4>📅 Rendez-vous programmé</h4>
                        <div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>Date:</strong> {$preinscription->date_rendez_vous->format('d/m/Y')}<br>
                            <strong>Heure:</strong> {$preinscription->heure_rendez_vous}<br>
                            <strong>Lieu:</strong> Centre de Formation Professionnelle, Douala
                        </div>

                        <div style='margin: 20px 0;'>
                            <a href='" . route('preinscription.confirmation', $preinscription) . "' class='btn'>📁 Voir mon dossier</a>
                        </div>

                        <p>Vous recevrez une notification dès que votre préinscription sera définitivement validée.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de rejet de paiement
     */
    private function generatePaiementRejectionEmail(Preinscription $preinscription, string $raison)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Paiement Non Validé</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .warning-box { background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545; }
                    .btn { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>❌ Paiement Non Validé</h1>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <div class='warning-box'>
                            <h3 style='margin-top: 0; color: #721c24;'>⚠️ Paiement Rejeté</h3>
                            <p style='margin-bottom: 0;'>Votre paiement pour la préinscription <strong>{$preinscription->numero_dossier}</strong> n'a pas pu être validé.</p>
                        </div>

                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>📝 Raison :</strong><br>
                            {$raison}
                        </div>

                        <p>Veuillez recommencer le processus de paiement en cliquant sur le bouton ci-dessous :</p>

                        <div style='margin: 20px 0;'>
                            <a href='" . route('payment.form', $preinscription) . "' class='btn'>🔄 Retenter le paiement</a>
                        </div>

                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>
                            <h4 style='margin-top: 0; color: #856404;'>💡 Conseils</h4>
                            <ul style='margin-bottom: 0;'>
                                <li>Vérifiez que votre compte Mobile Money dispose de suffisamment de fonds</li>
                                <li>Assurez-vous que votre numéro de téléphone est correct</li>
                                <li>Si le problème persiste, contactez votre opérateur mobile</li>
                            </ul>
                        </div>

                        <p style='margin-top: 20px;'>Si vous avez besoin d'assistance, n'hésitez pas à contacter notre service client.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>Support : 📞 +237 XXX XXX XXX | 📧 support@votredomaine.com</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de rappel de rendez-vous
     */
    private function generateRappelRendezVousEmail(Preinscription $preinscription)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Rappel de Rendez-vous</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .reminder-box { background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #6f42c1; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>📅 Rappel de Rendez-vous</h1>
                        <p>Votre rendez-vous approche</p>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <p>Nous vous rappelons votre rendez-vous de préinscription prévu :</p>
                        
                        <div class='reminder-box'>
                            <h3 style='margin-top: 0; color: #0056b3;'>🗓️ Rendez-vous Programmé</h3>
                            <div style='font-size: 18px; font-weight: bold;'>
                                📅 {$preinscription->date_rendez_vous->format('d/m/Y')}<br>
                                ⏰ {$preinscription->heure_rendez_vous}
                            </div>
                            <p style='margin-bottom: 0;'><strong>📍 Lieu :</strong> Centre de Formation Professionnelle, Douala</p>
                        </div>

                        <h4>📋 Documents à apporter</h4>
                        <ul>
                            <li>Pièce d'identité originale (CNI, Passeport)</li>
                            <li>Reçu de paiement des frais de préinscription</li>
                            <li>Dernier diplôme obtenu</li>
                            <li>2 photos d'identité récentes</li>
                        </ul>

                        <div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>💡 Important :</strong> Veuillez arriver 15 minutes avant l'heure du rendez-vous.
                        </div>

                        <p>Nous sommes impatients de vous rencontrer et de finaliser votre inscription.</p>

                        <p style='text-align: center; margin: 25px 0;'>
                            <strong>Service des admissions</strong><br>
                            📞 +237 XXX XXX XXX
                        </p>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de paiement en attente
     */
    private function generatePaiementEnAttenteEmail(Preinscription $preinscription)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Paiement en Attente</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .warning-box { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
                    .btn { display: inline-block; padding: 12px 24px; background: #ffc107; color: #212529; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>⏳ Paiement en Attente</h1>
                        <p>Finalisez votre préinscription</p>
                    </div>
                    
                    <div class='content'>
                        <p>Bonjour <strong>{$preinscription->prenom} {$preinscription->nom}</strong>,</p>
                        
                        <div class='warning-box'>
                            <h3 style='margin-top: 0; color: #856404;'>💳 Paiement Requis</h3>
                            <p style='margin-bottom: 0;'>Votre dossier <strong>{$preinscription->numero_dossier}</strong> est en attente de paiement.</p>
                        </div>

                        <p>Pour finaliser votre préinscription, veuillez effectuer le paiement des frais de <strong>5 000 FCFA</strong>.</p>

                        <h4>📊 Détails du paiement</h4>
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>Montant :</strong> 5 000 FCFA<br>
                            <strong>Modes acceptés :</strong> MTN Mobile Money, Orange Money<br>
                            <strong>Délai :</strong> 48 heures maximum
                        </div>

                        <div style='margin: 20px 0;'>
                            <a href='" . route('payment.form', $preinscription) . "' class='btn'>💳 Effectuer le paiement</a>
                        </div>

                        <div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>
                            <h4 style='margin-top: 0; color: #0056b3;'>📅 Rendez-vous programmé</h4>
                            <strong>Date:</strong> {$preinscription->date_rendez_vous->format('d/m/Y')}<br>
                            <strong>Heure:</strong> {$preinscription->heure_rendez_vous}<br>
                            <strong>Lieu:</strong> Centre de Formation Professionnelle, Douala
                        </div>

                        <p style='color: #dc3545; font-weight: bold;'>⚠️ Attention : Votre rendez-vous sera annulé si le paiement n'est pas effectué dans les délais.</p>

                        <p>Si vous avez déjà effectué le paiement, merci de l'ignorer.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . ". Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de notification aux administrateurs
     */
    private function generateAdminNotificationEmail(Preinscription $preinscription)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Nouvelle Préinscription</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .info-box { background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #6f42c1; }
                    .btn { display: inline-block; padding: 12px 24px; background: #6f42c1; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🆕 Nouvelle Préinscription</h1>
                        <p>Un nouveau dossier nécessite votre attention</p>
                    </div>
                    
                    <div class='content'>
                        <div class='info-box'>
                            <h3 style='margin-top: 0; color: #0056b3;'>📋 Dossier #{$preinscription->numero_dossier}</h3>
                            <p style='margin-bottom: 0;'><strong>Candidat :</strong> {$preinscription->prenom} {$preinscription->nom}</p>
                        </div>

                        <h4>👤 Informations personnelles</h4>
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>Email :</strong> {$preinscription->email}<br>
                            <strong>Téléphone :</strong> {$preinscription->telephone}<br>
                            <strong>Date de naissance :</strong> {$preinscription->date_naissance->format('d/m/Y')}<br>
                            <strong>Nationalité :</strong> {$preinscription->nationalite}
                        </div>

                        <h4>📅 Rendez-vous programmé</h4>
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>Date :</strong> {$preinscription->date_rendez_vous->format('d/m/Y')}<br>
                            <strong>Heure :</strong> {$preinscription->heure_rendez_vous}
                        </div>

                        <div style='margin: 20px 0;'>
                            <a href='" . route('admin.preinscriptions.show', $preinscription) . "' class='btn'>📁 Voir le dossier</a>
                        </div>

                        <p style='color: #666; font-size: 14px;'>Cette notification a été générée automatiquement.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Système de gestion des préinscriptions</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer le contenu email de notification de paiement validé aux administrateurs
     */
    private function generateAdminPaiementValideEmail(Paiement $paiement)
    {
        $preinscription = $paiement->preinscription;

        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Paiement Validé</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 30px; text-align: center; color: white; }
                    .content { padding: 30px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e9ecef; }
                    .success-box { background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                    .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>💰 Paiement Validé</h1>
                        <p>Nouvelle transaction confirmée</p>
                    </div>
                    
                    <div class='content'>
                        <div class='success-box'>
                            <h3 style='margin-top: 0; color: #155724;'>✅ Paiement Reçu</h3>
                            <p style='margin-bottom: 0;'>Le paiement pour le dossier <strong>{$preinscription->numero_dossier}</strong> a été validé.</p>
                        </div>

                        <h4>📊 Détails de la transaction</h4>
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>Montant :</strong> " . number_format($paiement->montant, 0, ',', ' ') . " FCFA<br>
                            <strong>Mode :</strong> " . strtoupper($paiement->mode_paiement) . "<br>
                            <strong>Référence :</strong> {$paiement->reference_paiement}<br>
                            <strong>Date :</strong> " . ($paiement->date_paiement ? $paiement->date_paiement->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i')) . "<br>
                            <strong>Agent :</strong> " . ($paiement->agent ? $paiement->agent->name : 'Système') . "
                        </div>

                        <h4>👤 Informations candidat</h4>
                        <div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>Nom :</strong> {$preinscription->prenom} {$preinscription->nom}<br>
                            <strong>Email :</strong> {$preinscription->email}<br>
                            <strong>Téléphone :</strong> {$preinscription->telephone}
                        </div>

                        <div style='margin: 20px 0;'>
                            <a href='" . route('admin.preinscriptions.show', $preinscription) . "' class='btn'>📁 Voir le dossier</a>
                        </div>
                    </div>
                    
                    <div class='footer'>
                        <p>Système de gestion des préinscriptions</p>
                        <p>&copy; " . date('Y') . " " . config('app.name') . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Générer un PDF de reçu par défaut
     */
    private function generateDefaultReceiptPdf(Preinscription $preinscription, array $data)
    {
        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Reçu de Préinscription</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                    .section { margin-bottom: 20px; }
                    .section-title { font-weight: bold; background: #f5f5f5; padding: 5px; }
                    .info-row { display: flex; margin-bottom: 5px; }
                    .info-label { font-weight: bold; width: 200px; }
                    .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>REÇU DE PRÉINSCRIPTION</h1>
                    <p>Numéro: {$preinscription->numero_dossier}</p>
                </div>

                <div class='section'>
                    <div class='section-title'>INFORMATIONS PERSONNELLES</div>
                    <div class='info-row'><span class='info-label'>Nom:</span> {$preinscription->nom}</div>
                    <div class='info-row'><span class='info-label'>Prénom:</span> {$preinscription->prenom}</div>
                    <div class='info-row'><span class='info-label'>Email:</span> {$preinscription->email}</div>
                    <div class='info-row'><span class='info-label'>Téléphone:</span> {$preinscription->telephone}</div>
                </div>

                <div class='section'>
                    <div class='section-title'>INFORMATIONS DE PAIEMENT</div>
                    " . ($preinscription->paiement ? "
                    <div class='info-row'><span class='info-label'>Référence:</span> {$preinscription->paiement->reference_paiement}</div>
                    <div class='info-row'><span class='info-label'>Montant:</span> " . number_format($preinscription->paiement->montant, 0, ',', ' ') . " FCFA</div>
                    <div class='info-row'><span class='info-label'>Statut:</span> {$preinscription->paiement->statut}</div>
                    " : "<div class='info-row'>Aucun paiement associé</div>") . "
                </div>

                <div class='footer'>
                    <p>Document généré le: {$data['date_emission']}</p>
                    <p>Ce document est généré automatiquement par le système</p>
                </div>
            </body>
            </html>
        ";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('recu-' . $preinscription->numero_dossier . '.pdf');
    }

    /**
     * Générer un PDF de détail par défaut
     */
    private function generateDefaultDetailPdf(Preinscription $preinscription, array $data)
    {
        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Détail du Dossier</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                    .section { margin-bottom: 20px; }
                    .section-title { font-weight: bold; background: #f5f5f5; padding: 5px; margin-bottom: 10px; }
                    .info-row { display: flex; margin-bottom: 5px; }
                    .info-label { font-weight: bold; width: 200px; }
                    .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>DOSSIER DE PRÉINSCRIPTION</h1>
                    <p>Numéro: {$preinscription->numero_dossier}</p>
                </div>

                <div class='section'>
                    <div class='section-title'>INFORMATIONS PERSONNELLES</div>
                    <div class='info-row'><span class='info-label'>Nom:</span> {$preinscription->nom}</div>
                    <div class='info-row'><span class='info-label'>Prénom:</span> {$preinscription->prenom}</div>
                    <div class='info-row'><span class='info-label'>Email:</span> {$preinscription->email}</div>
                    <div class='info-row'><span class='info-label'>Téléphone:</span> {$preinscription->telephone}</div>
                    <div class='info-row'><span class='info-label'>Date de naissance:</span> {$preinscription->date_naissance->format('d/m/Y')}</div>
                </div>

                <div class='section'>
                    <div class='section-title'>INFORMATIONS DE PAIEMENT</div>
                    " . ($preinscription->paiement ? "
                    <div class='info-row'><span class='info-label'>Référence:</span> {$preinscription->paiement->reference_paiement}</div>
                    <div class='info-row'><span class='info-label'>Montant:</span> " . number_format($preinscription->paiement->montant, 0, ',', ' ') . " FCFA</div>
                    <div class='info-row'><span class='info-label'>Statut:</span> {$preinscription->paiement->statut}</div>
                    " : "<div class='info-row'>Aucun paiement associé</div>") . "
                </div>

                <div class='section'>
                    <div class='section-title'>SUIVI ADMINISTRATIF</div>
                    <div class='info-row'><span class='info-label'>Statut:</span> {$preinscription->statut}</div>
                    <div class='info-row'><span class='info-label'>Agent:</span> " . ($preinscription->agent ? $preinscription->agent->name : 'Non assigné') . "</div>
                    <div class='info-row'><span class='info-label'>Commentaire:</span> {$preinscription->commentaire_agent}</div>
                </div>

                <div class='footer'>
                    <p>Document généré le: {$data['date_export']}</p>
                    <p>Ce document est généré automatiquement par le système</p>
                </div>
            </body>
            </html>
        ";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('dossier-' . $preinscription->numero_dossier . '.pdf');
    }
}