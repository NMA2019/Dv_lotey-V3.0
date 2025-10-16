<!-- resources/views/emails/preinscription-confirmation.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmation de Préinscription</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 30px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; }
        .dossier-number { font-size: 24px; font-weight: bold; color: #1e3c72; text-align: center; margin: 20px 0; }
        .info-box { background: white; border-left: 4px solid #1e3c72; padding: 15px; margin: 15px 0; }
        .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Préinscription Confirmée</h1>
            <p>Dv_lotey 2027 - Loterie Américaine</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $preinscription->prenom }}</strong>,</p>
            
            <p>Votre préinscription a été enregistrée avec succès. Voici le récapitulatif :</p>
            
            <div class="dossier-number">
                Votre numéro de dossier :<br>
                {{ $preinscription->numero_dossier }}
            </div>
            
            <div class="info-box">
                <strong>Informations Personnelles :</strong><br>
                {{ $preinscription->nom }} {{ $preinscription->prenom }}<br>
                {{ $preinscription->email }}<br>
                {{ $preinscription->telephone }}
            </div>
            
            <div class="info-box">
                <strong>Rendez-vous :</strong><br>
                Le {{ $preinscription->date_rendez_vous->format('d/m/Y') }} à {{ $preinscription->heure_rendez_vous }}<br>
                Centre de Formation Professionnelle, Logpom-Douala
            </div>
            
            <div class="info-box">
                <strong>Documents à apporter :</strong>
                <ul>
                    <li>Passeport valide (original + copie)</li>
                    <li>2 photos d'identité récentes</li>
                    <li>Justificatif de paiement</li>
                    <li>Copies des diplômes</li>
                </ul>
            </div>
            
            <p><strong>Important :</strong> Conservez précieusement ce numéro de dossier et tâchez de respecter l'heure du rendez-vous</p>
            
            <p>Pour toute question, contactez-nous à <a href="mailto:contact@dvlotey.com">ecolemondedigital@gmail.com</a></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Dv_lotey V2.0. Tous droits réservés.</p>
            <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>