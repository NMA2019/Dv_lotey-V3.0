<?php
// app/Http/Controllers/Public/PageController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Page d'accueil
     */
    public function accueil()
    {
        return view('public.accueil');
    }

    /**
     * Page "Bon à Savoir"
     */
    public function bonASavoir()
    {
        $documents = [
            [
                'titre' => 'Passeport Valide',
                'description' => 'Passeport en cours de validité avec au moins 6 mois avant expiration',
                'details' => 'Le passeport doit être lisible et en bon état'
            ],
            [
                'titre' => 'Photo d\'identité',
                'description' => 'Photo couleur récente format 5x5 cm',
                'details' => 'Photo prise sur fond blanc, visage bien visible sans accessoires'
            ],
            [
                'titre' => 'Diplômes',
                'description' => 'Copies certifiées des diplômes obtenus',
                'details' => 'Traduction officielle requise pour les diplômes non-français'
            ],
            [
                'titre' => 'Relevés de Notes',
                'description' => 'Relevés officiels des notes académiques',
                'details' => 'Doivent correspondre aux diplômes présentés'
            ]
        ];

        $qualitesPhoto = [
            'Format 5x5 cm sur fond blanc',
            'Photo récente (moins de 6 mois)',
            'Visage bien visible (80% de la photo)',
            'Regard direct vers l\'objectif',
            'Expression neutre, bouche fermée',
            'Pas de lunettes foncées',
            'Pas de couvre-chef (sauf raison religieuse)',
            'Bon contraste et éclairage'
        ];

        return view('public.bon-a-savoir', compact('documents', 'qualitesPhoto'));
    }

    /**
     * Page des Tarifs
     */
    public function tarifs()
    {
        $tarifs = [
            [
                'nom' => 'Forfait Standard',
                'prix' => 5000,
                'devise' => 'FCFA',
                'avantages' => [
                    'Préinscription complète',
                    'Vérification des documents',
                    'Soutien administratif',
                    'Délai de traitement : 48h'
                ],
                'recommandé' => false
            ],
            [
                'nom' => 'Forfait Premium',
                'prix' => 6500,
                'devise' => 'FCFA',
                'avantages' => [
                    'Tous les avantages Standard',
                    'Traitement express (24h)',
                    'Assistance téléphonique prioritaire',
                    'Revue approfondie du dossier',
                    'Conseils personnalisés'
                ],
                'recommandé' => true
            ],
            [
                'nom' => 'Forfait VIP',
                'prix' => 8000,
                'devise' => 'FCFA',
                'avantages' => [
                    'Tous les avantages Premium',
                    'Traitement immédiat',
                    'Accompagnement personnalisé',
                    'Support 7j/7',
                    'Garantie de satisfaction'
                ],
                'recommandé' => false
            ]
        ];

        return view('public.tarifs', compact('tarifs'));
    }

    /**
     * Page de Contact
     */
    public function contact()
    {
        $horaires = [
            ['jour' => 'Lundi - Vendredi', 'heures' => '08:00 - 17:00'],
            ['jour' => 'Samedi', 'heures' => '09:00 - 13:00'],
            ['jour' => 'Dimanche', 'heures' => 'Fermé']
        ];

        $coordonnees = [
            'adresse' => 'Centre de Formation Professionnelle du Commerce et du Monde Digital, Douala, Cameroun',
            'telephone' => '+237 679 449 165',
            'email' => 'ecolemodemondial@gmail.com',
            'horaires' => $horaires
        ];

        return view('public.contact', compact('coordonnees', 'horaires'));
    }

    /**
     * Traitement du formulaire de contact
     */
    public function envoyerMessage(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email',
            'sujet' => 'required|string|max:255',
            'message' => 'required|string|min:10'
        ]);

        // Ici vous pouvez ajouter l'envoi d'email
        // Mail::to('contact@dvlotey.com')->send(new ContactMessage($request->all()));

        return redirect()->route('contact')
            ->with('success', 'Votre message a été envoyé avec succès! Nous vous répondrons dans les plus brefs délais.');
    }
}