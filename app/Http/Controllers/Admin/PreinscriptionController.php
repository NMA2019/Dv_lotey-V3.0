<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\PreinscriptionConfirmation;
use App\Models\Paiement;
use App\Models\Preinscription;
use App\Models\User;
use App\Notifications\NewPreinscriptionNotification;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class PreinscriptionController extends Controller
{
    /**
     * Étape 1 - Informations personnelles
     */
    public function etape1()
    {
        // Réinitialiser la session si on recommence le processus
        if (request()->has('restart')) {
            session()->forget(['etape1', 'etape2', 'etape3']);
        }

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

        // Formater les données
        $validated['nom'] = strtoupper($validated['nom']);
        $validated['prenom'] = ucwords(strtolower($validated['prenom']));
        $validated['email'] = strtolower($validated['email']);

        session(['etape1' => $validated]);

        return redirect()->route('preinscription.etape2')
            ->with('success', 'Informations personnelles enregistrées avec succès!');
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

        // Validation supplémentaire pour la référence
        if (in_array($validated['mode_paiement'], ['mtn', 'orange']) && empty($validated['reference_paiement'])) {
            return redirect()->back()
                ->with('error', 'La référence de paiement est obligatoire pour le paiement mobile money.')
                ->withInput();
        }

        session(['etape2' => $validated]);

        return redirect()->route('preinscription.etape3')
            ->with('success', 'Mode de paiement sélectionné!');
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
        $creneauxDisponibles = $this->getCreneauxDisponibles();

        return view('public.preinscription.etape3', compact('datesDisponibles', 'creneauxDisponibles'));
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

        // Vérifier que la date n'est pas un weekend
        $dateRendezVous = Carbon::parse($validated['date_rendez_vous']);
        if ($dateRendezVous->isWeekend()) {
            return redirect()->back()
                ->with('error', 'Les rendez-vous ne sont pas disponibles le weekend.')
                ->withInput();
        }

        // Vérifier la disponibilité du créneau
        if (!$this->creneauDisponible($validated['date_rendez_vous'], $validated['heure_rendez_vous'])) {
            return redirect()->back()
                ->with('error', 'Ce créneau n\'est plus disponible. Veuillez choisir un autre horaire.')
                ->withInput();
        }

        // Vérifier que l'heure est dans les créneaux autorisés
        if (!$this->heureValide($validated['heure_rendez_vous'])) {
            return redirect()->back()
                ->with('error', 'L\'heure sélectionnée n\'est pas valide.')
                ->withInput();
        }

        session(['etape3' => $validated]);

        return redirect()->route('preinscription.recap')
            ->with('success', 'Rendez-vous planifié avec succès!');
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

        // Calcul du montant en fonction du mode de paiement
        $data['montant'] = $this->getMontantPaiement($data['etape2']['mode_paiement']);

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

            // Générer le numéro de dossier
            $numeroDossier = $this->genererNumeroDossier();

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
                'montant' => $this->getMontantPaiement($data['mode_paiement']),
                'statut' => 'en_attente',
                'date_paiement' => null
            ]);

            // Envoyer les notifications
            $this->envoyerNotifications($preinscription);

            // Nettoyer la session
            session()->forget(['etape1', 'etape2', 'etape3']);

            DB::commit();

            return redirect()->route('preinscription.confirmation', $preinscription)
                ->with('success', 'Préinscription soumise avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création de la préinscription: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('preinscription.etape1')
                ->with('error', 'Une erreur est survenue lors de la soumission. Veuillez réessayer. Code d\'erreur: ' . uniqid());
        }
    }

    /**
     * Page de confirmation
     */
    public function confirmation(Preinscription $preinscription)
    {
        // Vérifier que la préinscription appartient bien à l'utilisateur (via session ou autre mécanisme)
        // Pour l'instant, on affiche simplement la confirmation
        
        $paiement = $preinscription->paiement;

        return view('public.preinscription.confirmation', compact('preinscription', 'paiement'));
    }

    /**
     * Vérifier la disponibilité d'un créneau
     */
    private function creneauDisponible($date, $heure)
    {
        // Limite de 5 rendez-vous par créneau
        $count = Preinscription::where('date_rendez_vous', $date)
            ->where('heure_rendez_vous', $heure)
            ->where(function($query) {
                $query->where('statut', 'en_attente')
                      ->orWhere('statut', 'valide');
            })
            ->count();

        return $count < 5;
    }

    /**
     * Vérifier si l'heure est valide
     */
    private function heureValide($heure)
    {
        $creneauxAutorises = $this->getCreneauxDisponibles();
        return in_array($heure, array_keys($creneauxAutorises));
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
                $dates[$date->format('Y-m-d')] = [
                    'date' => $date->format('Y-m-d'),
                    'formatted' => $date->locale('fr')->isoFormat('ddd D MMM'),
                    'creneaux_disponibles' => $this->getCreneauxDisponiblesPourDate($date->format('Y-m-d'))
                ];
            }
        }

        return $dates;
    }

    /**
     * Obtenir les créneaux horaires disponibles
     */
    private function getCreneauxDisponibles()
    {
        return [
            '08:00' => '08:00 - 09:00',
            '09:00' => '09:00 - 10:00',
            '10:00' => '10:00 - 11:00',
            '11:00' => '11:00 - 12:00',
            '14:00' => '14:00 - 15:00',
            '15:00' => '15:00 - 16:00',
            '16:00' => '16:00 - 17:00',
        ];
    }

    /**
     * Obtenir les créneaux disponibles pour une date spécifique
     */
    private function getCreneauxDisponiblesPourDate($date)
    {
        $creneaux = $this->getCreneauxDisponibles();
        $creneauxOccupe = Preinscription::where('date_rendez_vous', $date)
            ->whereIn('statut', ['en_attente', 'valide'])
            ->pluck('heure_rendez_vous')
            ->toArray();

        return array_filter($creneaux, function($heure) use ($creneauxOccupe) {
            return !in_array($heure, $creneauxOccupe) || $this->creneauDisponible($date, $heure);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Générer un numéro de dossier unique
     */
    private function genererNumeroDossier()
    {
        $prefix = 'DV';
        $date = now()->format('ymd');
        
        do {
            $random = strtoupper(Str::random(6));
            $numeroDossier = "{$prefix}-{$date}-{$random}";
        } while (Preinscription::where('numero_dossier', $numeroDossier)->exists());

        return $numeroDossier;
    }

    /**
     * Obtenir le montant du paiement selon le mode
     */
    private function getMontantPaiement($modePaiement)
    {
        $tarifs = [
            'mtn' => 5000,
            'orange' => 5000,
            'espece' => 5000
        ];

        return $tarifs[$modePaiement] ?? 5000;
    }

    /**
     * Envoyer les notifications
     */
    private function envoyerNotifications(Preinscription $preinscription)
    {
        try {
            // Notification aux administrateurs et agents
            $admins = User::where('role', 'admin')->get();
            $agents = User::where('role', 'agent')->where('is_active', true)->get();
            
            $recipients = $admins->merge($agents);
            
            Notification::send($recipients, new NewPreinscriptionNotification($preinscription));

            // Email de confirmation au client
            Mail::to($preinscription->email)->send(new PreinscriptionConfirmation($preinscription));

            Log::info('Notifications envoyées pour la préinscription: ' . $preinscription->numero_dossier, [
                'preinscription_id' => $preinscription->id,
                'email_client' => $preinscription->email,
                'destinataires_notifications' => $recipients->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications: ' . $e->getMessage(), [
                'preinscription_id' => $preinscription->id,
                'error' => $e->getMessage()
            ]);
            
            // Ne pas bloquer le processus si les notifications échouent
        }
    }

    /**
     * Vérifier le statut d'une préinscription
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'numero_dossier' => 'required|string|max:20',
            'email' => 'required|email'
        ]);

        $preinscription = Preinscription::where('numero_dossier', $request->numero_dossier)
            ->where('email', $request->email)
            ->first();

        if (!$preinscription) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune préinscription trouvée avec ces informations.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'preinscription' => [
                'numero_dossier' => $preinscription->numero_dossier,
                'nom_complet' => $preinscription->nom_complet,
                'statut' => $preinscription->statut_label,
                'date_rendez_vous' => $preinscription->date_rendez_vous_complete,
                'date_soumission' => $preinscription->created_at->format('d/m/Y à H:i'),
                'commentaire' => $preinscription->commentaire_agent
            ]
        ]);
    }

    /**
     * Télécharger le reçu de préinscription
     */
    public function downloadReceipt(Preinscription $preinscription)
    {
        // Vérifier l'accès (à implémenter avec un token ou session)
        
        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement
        ];

        // return PDF::loadView('pdf.preinscription-receipt', $data)
        //           ->download('recu-' . $preinscription->numero_dossier . '.pdf');
        
        // Pour l'instant, retourner une vue
        return view('pdf.preinscription-receipt', $data);
    }
}