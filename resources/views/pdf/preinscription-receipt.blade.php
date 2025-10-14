<!-- resources/views/pdf/preinscription-receipt.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de Préinscription - {{ $preinscription->numero_dossier }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .content { margin: 20px 0; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .info-row { display: flex; margin-bottom: 5px; }
        .info-label { font-weight: bold; width: 200px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .signature { margin-top: 50px; border-top: 1px solid #333; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DV LOTEY USA - GREEN CARD</h1>
        <h2>REÇU DE PRÉINSCRIPTION</h2>
        <p>Numéro: {{ $preinscription->numero_dossier }}</p>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">INFORMATIONS PERSONNELLES</div>
            <div class="info-row">
                <div class="info-label">Nom complet:</div>
                <div>{{ $preinscription->nom }} {{ $preinscription->prenom }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div>{{ $preinscription->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone:</div>
                <div>{{ $preinscription->telephone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de naissance:</div>
                <div>{{ $preinscription->date_naissance->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">RENDEZ-VOUS</div>
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div>{{ $preinscription->date_rendez_vous->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Heure:</div>
                <div>{{ $preinscription->heure_rendez_vous }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Lieu:</div>
                <div>Centre de Formation Professionnelle, Douala</div>
            </div>
        </div>

        @if($paiement)
        <div class="section">
            <div class="section-title">INFORMATIONS DE PAIEMENT</div>
            <div class="info-row">
                <div class="info-label">Mode de paiement:</div>
                <div>{{ $paiement->mode_paiement_lisible }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Référence:</div>
                <div>{{ $paiement->reference_paiement ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Montant:</div>
                <div>{{ $paiement->montant_formate }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut:</div>
                <div>{{ $paiement->statut_label }}</div>
            </div>
        </div>
        @endif
    </div>

    <div class="signature">
        <p>Émis le: {{ $date_emission }}</p>
        <p>Signature du responsable</p>
        <p>_________________________</p>
    </div>

    <div class="footer">
        <p>DV LOTEY USA - Green Card | Email: contact@dvlotey.com | Tél: (+237) 679 449 165</p>
        <p>Ce document est généré automatiquement, toute falsification est passible de poursuites.</p>
    </div>
</body>
</html>