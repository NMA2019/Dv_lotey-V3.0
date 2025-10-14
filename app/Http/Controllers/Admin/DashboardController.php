<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\User;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Tableau de bord principal
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentPreinscriptions = $this->getRecentPreinscriptions();
        $rdvProchains = $this->getProchainsRendezVous();

        return view('admin.dashboard', compact('stats', 'recentPreinscriptions', 'rdvProchains'));
    }

    /**
     * Récupérer les statistiques du dashboard
     */
    private function getDashboardStats()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Stats pour l'admin
            $baseQuery = Preinscription::query();
        } else {
            // Stats pour l'agent (seulement ses préinscriptions)
            $baseQuery = Preinscription::where('agent_id', $user->id);
        }

        return [
            'total_preinscriptions' => $baseQuery->count(),
            'en_attente' => $baseQuery->clone()->enAttente()->count(),
            'validees' => $baseQuery->clone()->valides()->count(),
            'rejetees' => $baseQuery->clone()->rejetes()->count(),
            'reclasses' => $baseQuery->clone()->reclasses()->count(),
            'pour_aujourdhui' => Preinscription::pourAujourdhui()->count(),
            'paiements_attente' => Paiement::enAttente()->count(),
        ];
    }

    /**
     * Récupérer les préinscriptions récentes
     */
    private function getRecentPreinscriptions()
    {
        $user = auth()->user();
        $query = Preinscription::with('paiement')->latest();

        if (!$user->isAdmin()) {
            $query->where('agent_id', $user->id);
        }

        return $query->limit(10)->get();
    }

    /**
     * Récupérer les prochains rendez-vous
     */
    private function getProchainsRendezVous()
    {
        $user = auth()->user();
        $query = Preinscription::with('agent')
            ->whereDate('date_rendez_vous', '>=', today())
            ->orderBy('date_rendez_vous')
            ->orderBy('heure_rendez_vous');

        if (!$user->isAdmin()) {
            $query->where('agent_id', $user->id);
        }

        return $query->limit(5)->get();
    }

    /**
     * Statistiques avancées (pour les graphiques)
     */
    public function statistiques(Request $request)
    {
        $periode = $request->get('periode', 'mois');
        
        $stats = match($periode) {
            'semaine' => $this->getStatsSemaine(),
            'mois' => $this->getStatsMois(),
            'annee' => $this->getStatsAnnee(),
            default => $this->getStatsMois()
        };

        return response()->json($stats);
    }

    private function getStatsMois()
    {
        $debut = now()->startOfMonth();
        $fin = now()->endOfMonth();

        return [
            'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            'preinscriptions' => [45, 52, 38, 60],
            'validations' => [30, 45, 28, 50],
            'paiements' => [28, 40, 25, 45]
        ];
    }

    // Autres méthodes pour les statistiques...
}