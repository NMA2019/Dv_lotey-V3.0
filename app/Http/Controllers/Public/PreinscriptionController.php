<?php
// app/Http/Controllers/Public/PreinscriptionController.php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\Paiement;
use App\Services\NotificationService;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreinscriptionController extends Controller
{
    protected $notificationService;
    protected $paiementService;

    public function __construct(NotificationService $notificationService, PaiementService $paiementService)
    {
        $this->notificationService = $notificationService;
        $this->paiementService = $paiementService;
    }

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
            DB::beginTransaction();

            // Générer un numéro de dossier unique
            $numeroDossier = 'PR' . date('Ymd') . str_pad(Preinscription::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Créer la préinscription
            $preinscription = Preinscription::create(array_merge($data, [
                'numero_dossier' => $numeroDossier,
                'statut' => 'en_attente'
            ]));

            // Créer le paiement associé
            $paiement = Paiement::create([
                'preinscription_id' => $preinscription->id,
                'mode_paiement' => $data['mode_paiement'],
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'montant' => 5000, // Montant par défaut
                'statut' => 'en_attente'
            ]);

            // Envoyer la notification de confirmation
            $this->notificationService->sendPreinscriptionConfirmation($preinscription);

            // Nettoyer la session
            session()->forget(['etape1', 'etape2', 'etape3']);

            DB::commit();

            return redirect()->route('preinscription.confirmation', $preinscription);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la création de préinscription: ' . $e->getMessage());
            
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
     * Vérification du statut d'une préinscription
     */
    public function checkStatusPage()
    {
        return view('public.preinscription.verification-statut');
    }

    /**
     * Vérifier le statut d'une préinscription
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'numero_dossier' => 'required|string|max:255',
            'email' => 'required|email'
        ]);

        $preinscription = Preinscription::where('numero_dossier', $request->numero_dossier)
            ->where('email', $request->email)
            ->first();

        if (!$preinscription) {
            return redirect()->back()
                ->with('error', 'Aucune préinscription trouvée avec ces informations.')
                ->withInput();
        }

        return view('public.preinscription.statut-resultat', compact('preinscription'));
    }

    /**
     * Télécharger le reçu
     */
    public function downloadReceipt(Preinscription $preinscription)
    {
        // Vérifier que l'utilisateur a le droit de voir ce reçu
        if (!session()->has('preinscription_access') && !request()->hasValidSignature()) {
            abort(403, 'Accès non autorisé.');
        }

        // Générer le PDF du reçu
        return $this->notificationService->generateReceiptPdf($preinscription);
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
            $date = $today->copy()->addDays($i);
            
            // Exclure les weekends
            if (!$date->isWeekend()) {
                $dates[] = $date->format('Y-m-d');
            }
        }

        return $dates;
    }

    /**
     * Traitement du paiement mobile money
     */
    public function processPaiement(Request $request)
    {
        $request->validate([
            'preinscription_id' => 'required|exists:preinscriptions,id',
            'operator' => 'required|in:mtn,orange',
            'phone' => 'required|string|max:20'
        ]);

        try {
            $preinscription = Preinscription::findOrFail($request->preinscription_id);
            
            // Traiter le paiement via le service approprié
            $result = $this->paiementService->processMobilePayment(
                $preinscription,
                $request->operator,
                $request->phone,
                5000 // Montant
            );

            if ($result['success']) {
                return redirect()->route('preinscription.confirmation', $preinscription)
                    ->with('success', 'Paiement effectué avec succès!');
            } else {
                return redirect()->back()
                    ->with('error', 'Erreur lors du paiement: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du traitement du paiement: ' . $e->getMessage());
        }
    }
}