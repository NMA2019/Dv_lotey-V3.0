<?php
// app/Http/Controllers/Public/PreinscriptionController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PreinscriptionController extends Controller
{
    /**
     * Étape 1 - Informations personnelles
     */
    public function etape1()
    {
        return view('public.preinscription.etape1');
    }

    /**
     * Stockage des informations personnelles
     */
    public function storeEtape1(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date|before:-18 years',
            'lieu_naissance' => 'required|string|max:255',
            'nationalite' => 'required|string|max:255',
            'email' => 'required|email|unique:preinscriptions,email',
            'telephone' => 'required|string|max:20|unique:preinscriptions,telephone',
            'adresse' => 'required|string|max:500',
            'ville' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
        ], [
            'date_naissance.before' => 'Vous devez avoir au moins 18 ans pour vous inscrire.',
            'email.unique' => 'Cet email est déjà utilisé pour une préinscription.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé pour une préinscription.'
        ]);

        session(['etape1' => $validated]);

        return redirect()->route('preinscription.etape2');
    }

    /**
     * Étape 2 - Mode de paiement
     */
    public function etape2()
    {
        if (!session('etape1')) {
            return redirect()->route('preinscription.etape1')
                ->with('error', 'Veuillez compléter les informations personnelles d\'abord.');
        }

        return view('public.preinscription.etape2');
    }

    /**
     * Stockage du mode de paiement
     */
    public function storeEtape2(Request $request)
    {
        $validated = $request->validate([
            'mode_paiement' => 'required|in:mtn,orange,espece',
            'reference_paiement' => 'required_if:mode_paiement,mtn,orange|nullable|string|max:255',
        ], [
            'reference_paiement.required_if' => 'La référence de paiement est obligatoire pour les paiements mobile money.'
        ]);

        session(['etape2' => $validated]);

        return redirect()->route('preinscription.etape3');
    }

    /**
     * Étape 3 - Prise de rendez-vous
     */
    public function etape3()
    {
        if (!session('etape1') || !session('etape2')) {
            return redirect()->route('preinscription.etape1')
                ->with('error', 'Veuillez compléter les étapes précédentes d\'abord.');
        }

        // Dates disponibles (exclure weekends et dates passées)
        $datesDisponibles = $this->getDatesDisponibles();

        return view('public.preinscription.etape3', compact('datesDisponibles'));
    }

    /**
     * Stockage du rendez-vous
     */
    public function storeEtape3(Request $request)
    {
        $validated = $request->validate([
            'date_rendez_vous' => 'required|date|after:today',
            'heure_rendez_vous' => 'required|date_format:H:i',
        ], [
            'date_rendez_vous.after' => 'La date de rendez-vous doit être ultérieure à aujourd\'hui.',
            'heure_rendez_vous.date_format' => 'Le format de l\'heure est invalide.'
        ]);

        // Vérifier la disponibilité du créneau
        if (!$this->creneauDisponible($validated['date_rendez_vous'], $validated['heure_rendez_vous'])) {
            return redirect()->back()
                ->with('error', 'Ce créneau n\'est plus disponible. Veuillez choisir un autre horaire.')
                ->withInput();
        }

        session(['etape3' => $validated]);

        return redirect()->route('preinscription.recap');
    }

    /**
     * Récapitulatif avant soumission
     */
    public function recap()
    {
        if (!session('etape1') || !session('etape2') || !session('etape3')) {
            return redirect()->route('preinscription.etape1')
                ->with('error', 'Session expirée. Veuillez recommencer le processus.');
        }

        $data = [
            'etape1' => session('etape1'),
            'etape2' => session('etape2'),
            'etape3' => session('etape3')
        ];

        return view('public.preinscription.recap', compact('data'));
    }

    /**
     * Soumission finale du formulaire
     */
    public function store(Request $request)
    {
        if (!session('etape1') || !session('etape2') || !session('etape3')) {
            return redirect()->route('preinscription.etape1')
                ->with('error', 'Session expirée. Veuillez recommencer le processus.');
        }

        // Fusionner toutes les données
        $data = array_merge(
            session('etape1'),
            session('etape2'),
            session('etape3')
        );

        try {
            // Créer la préinscription
            $preinscription = Preinscription::create($data);

            // Créer le paiement associé
            $paiement = Paiement::create([
                'preinscription_id' => $preinscription->id,
                'mode_paiement' => $data['mode_paiement'],
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'montant' => 5000, // Montant par défaut
                'statut' => 'en_attente'
            ]);

            // Nettoyer la session
            session()->forget(['etape1', 'etape2', 'etape3']);

            // Ici vous pouvez ajouter l'envoi de notifications
            // $this->envoyerNotifications($preinscription);

            return redirect()->route('preinscription.confirmation', $preinscription);

        } catch (\Exception $e) {
            return redirect()->route('preinscription.etape1')
                ->with('error', 'Une erreur est survenue lors de la soumission. Veuillez réessayer.');
        }
    }

    /**
     * Page de confirmation
     */
    public function confirmation(Preinscription $preinscription)
    {
        return view('public.preinscription.confirmation', compact('preinscription'));
    }

    /**
     * Vérifier la disponibilité d'un créneau
     */
    private function creneauDisponible($date, $heure)
    {
        // Limite de 5 rendez-vous par créneau
        $count = Preinscription::where('date_rendez_vous', $date)
            ->where('heure_rendez_vous', $heure)
            ->count();

        return $count < 5;
    }

    /**
     * Générer les dates disponibles
     */
    private function getDatesDisponibles()
    {
        $dates = [];
        $today = now();
        
        for ($i = 1; $i <= 14; $i++) {
            $date = $today->addDay();
            
            // Exclure les weekends
            if (!$date->isWeekend()) {
                $dates[] = $date->format('Y-m-d');
            }
        }

        return $dates;
    }

    /**
     * Envoyer les notifications (à implémenter)
     */
    private function envoyerNotifications(Preinscription $preinscription)
    {
        // Notification aux administrateurs
        // Notification::send(User::admin()->get(), new NewPreinscriptionNotification($preinscription));
        
        // Email de confirmation au client
        // Mail::to($preinscription->email)->send(new PreinscriptionConfirmation($preinscription));
    }
}