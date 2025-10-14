<?php
// app/Http/Controllers/Admin/PdfController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function preinscriptionReceipt(Preinscription $preinscription)
    {
        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement,
            'date_emission' => now()->format('d/m/Y à H:i')
        ];

        $pdf = Pdf::loadView('pdf.preinscription-receipt', $data);
        
        return $pdf->download('recu-' . $preinscription->numero_dossier . '.pdf');
    }

    public function preinscriptionDetail(Preinscription $preinscription)
    {
        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement,
            'agent' => $preinscription->agent
        ];

        $pdf = Pdf::loadView('pdf.preinscription-detail', $data);
        
        return $pdf->download('dossier-' . $preinscription->numero_dossier . '.pdf');
    }

    public function exportPreinscriptions(Request $request)
    {
        $query = Preinscription::with(['paiement', 'agent']);
        
        // Appliquer les filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('created_at', [$request->date_debut, $request->date_fin]);
        }

        $preinscriptions = $query->get();

        $data = [
            'preinscriptions' => $preinscriptions,
            'filtres' => $request->all(),
            'date_export' => now()->format('d/m/Y à H:i')
        ];

        $pdf = Pdf::loadView('pdf.preinscriptions-list', $data);
        
        return $pdf->download('preinscriptions-' . now()->format('Y-m-d') . '.pdf');
    }

    public function paiementsReport(Request $request)
    {
        $query = Paiement::with(['preinscription', 'agent']);
        
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        }

        $paiements = $query->get();

        $data = [
            'paiements' => $paiements,
            'total_montant' => $paiements->sum('montant'),
            'filtres' => $request->all(),
            'date_export' => now()->format('d/m/Y à H:i')
        ];

        $pdf = Pdf::loadView('pdf.paiements-report', $data);
        
        return $pdf->download('rapport-paiements-' . now()->format('Y-m-d') . '.pdf');
    }
}