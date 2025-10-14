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
            'en_attente' => Preinscription::where('statut', 'en_attente')->count(),
            'valides' => Preinscription::where('statut', 'valide')->count(),
            'rejetees' => Preinscription::where('statut', 'rejete')->count(),
            'reclasses' => Preinscription::where('statut', 'reclasse')->count(),
            
            // Stats paiements
            'paiements_attente' => Paiement::where('statut', 'en_attente')->count(),
            'paiements_valides' => Paiement::where('statut', 'valide')->count(),
            'paiements_rejetes' => Paiement::where('statut', 'rejete')->count(),
            'revenus_mois' => Paiement::where('statut', 'valide')
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->sum('montant'),
            
            // Stats utilisateurs
            'total_agents' => User::where('role', 'agent')->count(),
            'agents_actifs' => User::where('role', 'agent')->where('is_active', true)->count(),
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
            'revenus_jour' => Paiement::where('statut', 'valide')
                                ->whereDate('date_paiement', today())
                                ->sum('montant'),
            'revenus_semaine' => Paiement::where('statut', 'valide')
                                ->whereBetween('date_paiement', [now()->startOfWeek(), now()->endOfWeek()])
                                ->sum('montant'),
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
                            'nom_complet' => $preinscription->nom . ' ' . $preinscription->prenom,
                            'numero_dossier' => $preinscription->numero_dossier,
                            'heure_rendez_vous' => $preinscription->heure_rendez_vous,
                            'statut_css_class' => $this->getStatutCssClass($preinscription->statut),
                            'statut_label' => $this->getStatutLabel($preinscription->statut)
                        ];
                    });
    }

    // Méthodes utilitaires pour les statuts
    private function getStatutCssClass($statut)
    {
        $classes = [
            'en_attente' => 'warning',
            'valide' => 'success',
            'rejete' => 'danger',
            'reclasse' => 'info'
        ];

        return $classes[$statut] ?? 'secondary';
    }

    private function getStatutLabel($statut)
    {
        $labels = [
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            'reclasse' => 'Reclassé'
        ];

        return $labels[$statut] ?? $statut;
    }

    public function getStatsApi(Request $request)
    {
        try {
            $periode = $request->get('periode', 'month');
            
            $stats = match($periode) {
                'week' => $this->getWeeklyStats(),
                'month' => $this->getMonthlyStats(),
                'year' => $this->getYearlyStats(),
                default => $this->getMonthlyStats()
            };

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur API stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getWeeklyStats()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return [
            'preinscriptions' => Preinscription::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'paiements_valides' => Paiement::where('statut', 'valide')
                                    ->whereBetween('date_paiement', [$startOfWeek, $endOfWeek])
                                    ->count(),
            'revenus' => Paiement::where('statut', 'valide')
                            ->whereBetween('date_paiement', [$startOfWeek, $endOfWeek])
                            ->sum('montant'),
            'rendez_vous' => Preinscription::whereBetween('date_rendez_vous', [$startOfWeek, $endOfWeek])->count()
        ];
    }

    private function getMonthlyStats()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return [
            'preinscriptions' => Preinscription::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'paiements_valides' => Paiement::where('statut', 'valide')
                                    ->whereBetween('date_paiement', [$startOfMonth, $endOfMonth])
                                    ->count(),
            'revenus' => Paiement::where('statut', 'valide')
                            ->whereBetween('date_paiement', [$startOfMonth, $endOfMonth])
                            ->sum('montant'),
            'rendez_vous' => Preinscription::whereBetween('date_rendez_vous', [$startOfMonth, $endOfMonth])->count()
        ];
    }

    private function getYearlyStats()
    {
        $startOfYear = now()->startOfYear();
        $endOfYear = now()->endOfYear();

        return [
            'preinscriptions' => Preinscription::whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
            'paiements_valides' => Paiement::where('statut', 'valide')
                                    ->whereBetween('date_paiement', [$startOfYear, $endOfYear])
                                    ->count(),
            'revenus' => Paiement::where('statut', 'valide')
                            ->whereBetween('date_paiement', [$startOfYear, $endOfYear])
                            ->sum('montant'),
            'rendez_vous' => Preinscription::whereBetween('date_rendez_vous', [$startOfYear, $endOfYear])->count()
        ];
    }

    // Nouvelle méthode pour les stats détaillées
    public function getDetailedStats(Request $request)
    {
        try {
            $dateDebut = $request->get('date_debut', now()->subDays(30)->format('Y-m-d'));
            $dateFin = $request->get('date_fin', now()->format('Y-m-d'));

            $stats = [
                'periode' => [
                    'debut' => $dateDebut,
                    'fin' => $dateFin
                ],
                'preinscriptions' => [
                    'total' => Preinscription::whereBetween('created_at', [$dateDebut, $dateFin])->count(),
                    'par_statut' => Preinscription::select('statut', DB::raw('COUNT(*) as count'))
                                        ->whereBetween('created_at', [$dateDebut, $dateFin])
                                        ->groupBy('statut')
                                        ->pluck('count', 'statut')
                                        ->toArray(),
                    'evolution' => $this->getEvolutionPreinscriptions($dateDebut, $dateFin)
                ],
                'paiements' => [
                    'total' => Paiement::whereBetween('created_at', [$dateDebut, $dateFin])->count(),
                    'par_statut' => Paiement::select('statut', DB::raw('COUNT(*) as count'))
                                    ->whereBetween('created_at', [$dateDebut, $dateFin])
                                    ->groupBy('statut')
                                    ->pluck('count', 'statut')
                                    ->toArray(),
                    'revenus' => Paiement::where('statut', 'valide')
                                ->whereBetween('date_paiement', [$dateDebut, $dateFin])
                                ->sum('montant'),
                    'par_mode' => Paiement::select('mode_paiement', DB::raw('COUNT(*) as count'))
                                    ->whereBetween('created_at', [$dateDebut, $dateFin])
                                    ->groupBy('mode_paiement')
                                    ->pluck('count', 'mode_paiement')
                                    ->toArray()
                ],
                'rendez_vous' => [
                    'total' => Preinscription::whereBetween('date_rendez_vous', [$dateDebut, $dateFin])->count(),
                    'complets' => Preinscription::whereBetween('date_rendez_vous', [$dateDebut, $dateFin])
                                    ->whereIn('statut', ['valide', 'rejete', 'reclasse'])
                                    ->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur stats détaillées: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques détaillées'
            ], 500);
        }
    }

    private function getEvolutionPreinscriptions($dateDebut, $dateFin)
    {
        $evolution = Preinscription::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('created_at', [$dateDebut, $dateFin])
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return $evolution->pluck('count', 'date')->toArray();
    }
}