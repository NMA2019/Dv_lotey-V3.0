<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\Paiement;
use App\Models\User;
use App\Models\Creneau;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Log;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStatsGlobales();
        $todayStats = $this->getTodayStats();
        $monthlyData = $this->getMonthlyData();
        $recentPreinscriptions = $this->getRecentPreinscriptions();
        $rdvAujourdhui = $this->getRdvAujourdhui();
        $paiementsStats = $this->getPaiementsStats();

        return view('admin.dashboard', compact(
            'stats',
            'todayStats', 
            'monthlyData',
            'recentPreinscriptions',
            'rdvAujourdhui',
            'paiementsStats'
        ));
    }

    private function getStatsGlobales()
    {
        return [
            'total_preinscriptions' => Preinscription::count(),
            'en_attente' => Preinscription::enAttente()->count(),
            'valides' => Preinscription::valides()->count(),
            'rejetees' => Preinscription::rejetees()->count(),
            'reclasses' => Preinscription::reclasses()->count(),
            
            // Stats paiements
            'paiements_attente' => Paiement::enAttente()->count(),
            'paiements_valides' => Paiement::valides()->count(),
            'paiements_rejetes' => Paiement::rejetes()->count(),
            'revenus_mois' => Paiement::valides()->thisMonth()->sum('montant'),
            
            // Stats utilisateurs
            'total_agents' => User::agent()->active()->count(),
            'agents_actifs' => User::agent()->active()->count(),
        ];
    }

    private function getTodayStats()
    {
        return [
            'nouvelles' => Preinscription::whereDate('created_at', today())->count(),
            'traitees' => Preinscription::whereDate('updated_at', today())
                            ->whereIn('statut', ['valide', 'rejete', 'reclasse'])
                            ->count(),
            'rendez_vous' => Preinscription::whereDate('date_rendez_vous', today())->count(),
            'paiements' => Paiement::whereDate('created_at', today())->count(),
        ];
    }

    private function getMonthlyData()
    {
        $data = Preinscription::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(CASE WHEN statut = "valide" THEN 1 ELSE 0 END) as valides')
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $monthly = array_fill(1, 12, 0);
        $valides = array_fill(1, 12, 0);

        foreach ($data as $item) {
            $monthly[$item->month] = $item->count;
            $valides[$item->month] = $item->valides;
        }

        return [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            'total' => array_values($monthly),
            'valides' => array_values($valides)
        ];
    }

    private function getPaiementsStats()
    {
        return [
            'par_mode' => Paiement::select('mode_paiement', DB::raw('COUNT(*) as count'))
                            ->groupBy('mode_paiement')
                            ->pluck('count', 'mode_paiement')
                            ->toArray(),
            'revenus_jour' => Paiement::valides()->today()->sum('montant'),
            'revenus_semaine' => Paiement::valides()->whereBetween('date_paiement', [now()->startOfWeek(), now()->endOfWeek()])->sum('montant'),
        ];
    }

    private function getRecentPreinscriptions()
    {
        return Preinscription::with(['paiement', 'agent'])
                    ->latest()
                    ->limit(10)
                    ->get();
    }

    private function getRdvAujourdhui()
    {
        return Preinscription::with(['paiement'])
                    ->whereDate('date_rendez_vous', today())
                    ->orderBy('heure_rendez_vous')
                    ->get()
                    ->map(function($preinscription) {
                        return (object)[
                            'nom_complet' => $preinscription->nom_complet,
                            'numero_dossier' => $preinscription->numero_dossier,
                            'heure_rendez_vous' => $preinscription->heure_rendez_vous,
                            'statut_css_class' => $preinscription->statut_css_class,
                            'statut_label' => $preinscription->statut_label
                        ];
                    });
    }

    public function getStatsApi(Request $request)
    {
        $periode = $request->get('periode', 'month');
        
        $stats = match($periode) {
            'week' => $this->getWeeklyStats(),
            'month' => $this->getMonthlyStats(),
            'year' => $this->getYearlyStats(),
            default => $this->getMonthlyStats()
        };

        return response()->json($stats);
    }

    private function getWeeklyStats()
    {
        // Implémentation des stats hebdomadaires
        return [];
    }
}