<?php
// app/Http/Controllers/Admin/PreinscriptionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\Paiement;
use App\Models\User;
use App\Models\Creneau;
use App\Services\NotificationService;
use App\Services\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * Liste des préinscriptions
     */
    public function index(Request $request)
    {
        $query = Preinscription::query();

        if ($request->filled('include_paiement') || $request->filled('include_agent')) {
            $relations = [];
            if ($request->filled('include_paiement')) {
                $relations[] = 'paiement';
            }
            if ($request->filled('include_agent')) {
                $relations[] = 'agent';
            }
            $query->with($relations);
        }

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_dossier', 'LIKE', "%{$search}%")
                  ->orWhere('nom', 'LIKE', "%{$search}%")
                  ->orWhere('prenom', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('telephone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('created_at', [
                $request->date_debut,
                $request->date_fin
            ]);
        }

        // Si agent, ne voir que ses préinscriptions
        if (auth()->check() && auth()->user()->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        $preinscriptions = $query->latest()->paginate(20);

        $stats = Preinscription::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN statut = "en_attente" THEN 1 END) as en_attente,
            COUNT(CASE WHEN statut = "valide" THEN 1 END) as valides,
            COUNT(CASE WHEN statut = "rejete" THEN 1 END) as rejetees
        ')->first()->toArray();

        return view('admin.preinscriptions.index', compact('preinscriptions', 'stats'));
    }

    /**
     * Afficher une préinscription
     */
    public function show(Preinscription $preinscription)
    {
        // Vérifier les permissions
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $preinscription->load(['paiement', 'agent']);

        return view('admin.preinscriptions.show', compact('preinscription'));
    }

    /**
     * Mettre à jour une préinscription
     */
    public function update(Request $request, Preinscription $preinscription)
    {
        $validated = $request->validate([
            'commentaire_agent' => 'nullable|string|max:1000',
            'statut' => 'sometimes|in:en_attente,valide,rejete,reclasse'
        ]);

        $preinscription->update($validated);

        return redirect()->back()
            ->with('success', 'Préinscription mise à jour avec succès!');
    }

    /**
     * Valider une préinscription
     */
    public function valider(Request $request, Preinscription $preinscription)
    {
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'commentaire' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $preinscription->update([
                'statut' => 'valide',
                'agent_id' => auth()->id(),
                'commentaire_agent' => $request->commentaire,
                'date_traitement' => now()
            ]);

            // Envoyer la notification de validation
            $this->notificationService->sendValidationNotification($preinscription);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Préinscription validée avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la validation de la préinscription: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->back()
                ->with('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une préinscription
     */
    public function rejeter(Request $request, Preinscription $preinscription)
    {
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'commentaire' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $preinscription->update([
                'statut' => 'rejete',
                'agent_id' => auth()->id(),
                'commentaire_agent' => $request->commentaire,
                'date_traitement' => now()
            ]);

            // Envoyer la notification de rejet
            $this->notificationService->sendRejectionNotification($preinscription);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Préinscription rejetée avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors du rejet: ' . $e->getMessage());
        }
    }

    /**
     * Reclasser une préinscription
     */
    public function reclasser(Request $request, Preinscription $preinscription)
    {
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'commentaire' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $preinscription->update([
                'statut' => 'reclasse',
                'agent_id' => auth()->id(),
                'commentaire_agent' => $request->commentaire,
                'date_traitement' => now()
            ]);

            // Envoyer la notification de reclassement
            $this->notificationService->sendReclassificationNotification($preinscription);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Préinscription reclassée avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors du reclassement: ' . $e->getMessage());
        }
    }

    /**
     * Mettre en attente une préinscription
     */
    public function mettreEnAttente(Request $request, Preinscription $preinscription)
    {
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'commentaire' => 'required|string|max:1000'
        ]);

        try {
            $preinscription->update([
                'statut' => 'en_attente',
                'agent_id' => auth()->id(),
                'commentaire_agent' => $request->commentaire
            ]);

            return redirect()->back()
                ->with('success', 'Préinscription mise en attente avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise en attente: ' . $e->getMessage());
        }
    }

    /**
     * Gestion des paiements
     */
    public function paiements(Request $request)
    {
        $query = Paiement::with(['preinscription', 'agent']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_paiement', 'LIKE', "%{$search}%")
                  ->orWhereHas('preinscription', function($q) use ($search) {
                      $q->where('numero_dossier', 'LIKE', "%{$search}%")
                        ->orWhere('nom', 'LIKE', "%{$search}%")
                        ->orWhere('prenom', 'LIKE', "%{$search}%");
                  });
            });
        }

        $paiements = $query->latest()->paginate(20);
        
        $stats = Paiement::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN statut = "en_attente" THEN 1 END) as en_attente,
            COUNT(CASE WHEN statut = "valide" THEN 1 END) as valides,
            COUNT(CASE WHEN statut = "rejete" THEN 1 END) as rejetes
        ')->first()->toArray();

        return view('admin.paiements.index', compact('paiements', 'stats'));
    }

    /**
     * Valider un paiement
     */
    public function validerPaiement(Request $request, Paiement $paiement)
    {
        $request->validate([
            'commentaire' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $paiement->update([
                'statut' => 'valide',
                'agent_id' => auth()->id(),
                'date_paiement' => now(),
                'commentaire' => $request->commentaire
            ]);

            // Si le paiement est validé, envoyer une notification
            if ($paiement->preinscription) {
                $this->notificationService->sendPaiementValidationNotification($paiement->preinscription);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Paiement validé avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter un paiement
     */
    public function rejeterPaiement(Request $request, Paiement $paiement)
    {
        $request->validate([
            'commentaire' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $paiement->update([
                'statut' => 'rejete',
                'agent_id' => auth()->id(),
                'commentaire' => $request->commentaire
            ]);

            // Si le paiement est rejeté, envoyer une notification
            if ($paiement->preinscription) {
                $this->notificationService->sendPaiementRejectionNotification($paiement->preinscription, $request->commentaire);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Paiement rejeté avec succès!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors du rejet: ' . $e->getMessage());
        }
    }

    /**
     * Gestion du calendrier
     */
    public function calendrier()
    {
        $creneaux = Creneau::where('date_creneau', '>=', today())
                    ->orderBy('date_creneau')
                    ->orderBy('heure_debut')
                    ->paginate(30);

        return view('admin.calendrier.index', compact('creneaux'));
    }

    /**
     * Générer des créneaux
     */
    public function genererCreneaux(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut'
        ]);

        try {
            $creneaux = Creneau::genererCreneaux($request->date_debut, $request->date_fin);

            return redirect()->back()
                ->with('success', count($creneaux) . ' créneaux générés avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour un créneau
     */
    public function updateCreneau(Request $request, Creneau $creneau)
    {
        $validated = $request->validate([
            'capacite_max' => 'required|integer|min:1|max:100',
            'est_actif' => 'boolean'
        ]);

        try {
            $creneau->update($validated);

            return redirect()->back()
                ->with('success', 'Créneau mis à jour avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du créneau: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel des préinscriptions
     */
    public function exporter(Request $request)
    {
        $query = Preinscription::with(['paiement', 'agent']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('created_at', [
                $request->date_debut,
                $request->date_fin
            ]);
        }

        if (auth()->user()->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        $preinscriptions = $query->get();

        return view('admin.preinscriptions.export', compact('preinscriptions'));
    }

    /**
     * Tester les notifications
     */
    public function testerNotifications(Request $request, Preinscription $preinscription)
    {
        try {
            $type = $request->type;
            
            switch ($type) {
                case 'confirmation':
                    $result = $this->notificationService->sendPreinscriptionConfirmation($preinscription);
                    break;
                case 'validation':
                    $result = $this->notificationService->sendValidationNotification($preinscription);
                    break;
                case 'rejet':
                    $result = $this->notificationService->sendRejectionNotification($preinscription);
                    break;
                case 'reclassement':
                    $result = $this->notificationService->sendReclassificationNotification($preinscription);
                    break;
                default:
                    return redirect()->back()
                        ->with('error', 'Type de notification non valide.');
            }

            if ($result) {
                return redirect()->back()
                    ->with('success', 'Notification de test envoyée avec succès!');
            } else {
                return redirect()->back()
                    ->with('error', 'Erreur lors de l\'envoi de la notification.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}