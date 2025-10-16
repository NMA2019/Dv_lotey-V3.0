<?php
// app/Http\Controllers\Admin\PdfController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscription;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class PdfController extends Controller
{
    public function preinscriptionReceipt(Preinscription $preinscription)
    {
        // Vérifier les permissions si nécessaire
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement,
            'date_emission' => now()->format('d/m/Y à H:i')
        ];

        try {
            $pdf = Pdf::loadView('pdf.preinscription-receipt', $data);
            return $pdf->download('recu-' . $preinscription->numero_dossier . '.pdf');
        } catch (\InvalidArgumentException $e) {
            // Si la vue n'existe pas, utiliser une vue par défaut
            return $this->generateDefaultReceipt($preinscription, $data);
        }
    }

    public function preinscriptionDetail(Preinscription $preinscription)
    {
        // Vérifier les permissions si nécessaire
        if (auth()->user()->isAgent() && $preinscription->agent_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $data = [
            'preinscription' => $preinscription,
            'paiement' => $preinscription->paiement,
            'agent' => $preinscription->agent
        ];

        try {
            $pdf = Pdf::loadView('pdf.preinscription-detail', $data);
            return $pdf->download('dossier-' . $preinscription->numero_dossier . '.pdf');
        } catch (\InvalidArgumentException $e) {
            // Si la vue n'existe pas, utiliser une vue par défaut
            return $this->generateDefaultDetail($preinscription, $data);
        }
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

        // Si agent, ne voir que ses préinscriptions
        if (auth()->user()->isAgent()) {
            $query->where('agent_id', auth()->id());
        }

        $preinscriptions = $query->get();

        $data = [
            'preinscriptions' => $preinscriptions,
            'filtres' => $request->all(),
            'date_export' => now()->format('d/m/Y à H:i')
        ];

        try {
            $pdf = Pdf::loadView('pdf.preinscriptions-list', $data);
            return $pdf->download('preinscriptions-' . now()->format('Y-m-d') . '.pdf');
        } catch (\InvalidArgumentException $e) {
            // Si la vue n'existe pas, utiliser une vue par défaut
            return $this->generateDefaultPreinscriptionsList($preinscriptions, $data);
        }
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

        try {
            $pdf = Pdf::loadView('pdf.paiements-report', $data);
            return $pdf->download('rapport-paiements-' . now()->format('Y-m-d') . '.pdf');
        } catch (\InvalidArgumentException $e) {
            // Si la vue n'existe pas, utiliser une vue par défaut
            return $this->generateDefaultPaiementsReport($paiements, $data);
        }
    }

    /**
     * Génère un reçu par défaut si la vue n'existe pas
     */
    private function generateDefaultReceipt(Preinscription $preinscription, $data)
    {
        $datePaiement = $preinscription->paiement && $preinscription->paiement->date_paiement 
            ? $preinscription->paiement->date_paiement->format('d/m/Y')
            : 'N/A';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Reçu de Préinscription</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .section { margin-bottom: 20px; }
                .section-title { font-weight: bold; background: #f5f5f5; padding: 5px; }
                .info-row { display: flex; margin-bottom: 5px; }
                .info-label { font-weight: bold; width: 200px; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>REÇU DE PRÉINSCRIPTION</h1>
                <p>Numéro: {$preinscription->numero_dossier}</p>
            </div>
            
            <div class='section'>
                <div class='section-title'>INFORMATIONS PERSONNELLES</div>
                <div class='info-row'><span class='info-label'>Nom:</span> {$preinscription->nom}</div>
                <div class='info-row'><span class='info-label'>Prénom:</span> {$preinscription->prenom}</div>
                <div class='info-row'><span class='info-label'>Email:</span> {$preinscription->email}</div>
                <div class='info-row'><span class='info-label'>Téléphone:</span> {$preinscription->telephone}</div>
            </div>
            
            <div class='section'>
                <div class='section-title'>INFORMATIONS DE PAIEMENT</div>
                " . ($preinscription->paiement ? "
                <div class='info-row'><span class='info-label'>Référence:</span> {$preinscription->paiement->reference_paiement}</div>
                <div class='info-row'><span class='info-label'>Montant:</span> {$preinscription->paiement->montant} FCFA</div>
                <div class='info-row'><span class='info-label'>Statut:</span> {$preinscription->paiement->statut}</div>
                <div class='info-row'><span class='info-label'>Date paiement:</span> {$datePaiement}</div>
                " : "<div class='info-row'>Aucun paiement associé</div>") . "
            </div>
            
            <div class='footer'>
                <p>Document généré le: {$data['date_emission']}</p>
                <p>Ce document est généré automatiquement par le système</p>
            </div>
        </body>
        </html>";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('recu-' . $preinscription->numero_dossier . '.pdf');
    }

    /**
     * Génère une liste de préinscriptions par défaut
     */
    private function generateDefaultPreinscriptionsList($preinscriptions, $data)
    {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Liste des Préinscriptions</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>LISTE DES PRÉINSCRIPTIONS</h1>
                <p>Export généré le: {$data['date_export']}</p>
                <p>Total: " . count($preinscriptions) . " préinscriptions</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>N° Dossier</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Date Création</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($preinscriptions as $preinscription) {
            $html .= "
                    <tr>
                        <td>{$preinscription->numero_dossier}</td>
                        <td>{$preinscription->nom}</td>
                        <td>{$preinscription->prenom}</td>
                        <td>{$preinscription->email}</td>
                        <td>{$preinscription->telephone}</td>
                        <td>{$preinscription->statut}</td>
                        <td>{$preinscription->created_at->format('d/m/Y')}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>
            
            <div class='footer'>
                <p>Document généré automatiquement par le système</p>
            </div>
        </body>
        </html>";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('preinscriptions-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Génère un rapport de paiements par défaut
     */
    private function generateDefaultPaiementsReport($paiements, $data)
    {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Rapport des Paiements</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>RAPPORT DES PAIEMENTS</h1>
                <p>Export généré le: {$data['date_export']}</p>
            </div>
            
            <div class='summary'>
                <p><strong>Total des paiements:</strong> " . count($paiements) . "</p>
                <p><strong>Montant total:</strong> {$data['total_montant']} FCFA</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Dossier</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date Paiement</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($paiements as $paiement) {
            $datePaiement = $paiement->date_paiement 
                ? $paiement->date_paiement->format('d/m/Y')
                : 'N/A';

            $html .= "
                    <tr>
                        <td>{$paiement->reference_paiement}</td>
                        <td>{$paiement->preinscription->numero_dossier}</td>
                        <td>{$paiement->preinscription->nom}</td>
                        <td>{$paiement->preinscription->prenom}</td>
                        <td>{$paiement->montant} FCFA</td>
                        <td>{$paiement->statut}</td>
                        <td>{$datePaiement}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>
            
            <div class='footer'>
                <p>Document généré automatiquement par le système</p>
            </div>
        </body>
        </html>";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('rapport-paiements-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Génère un détail par défaut si la vue n'existe pas
     */
    private function generateDefaultDetail(Preinscription $preinscription, $data)
    {
        $datePaiement = $preinscription->paiement && $preinscription->paiement->date_paiement 
            ? $preinscription->paiement->date_paiement->format('d/m/Y')
            : 'N/A';

        $dateTraitement = $preinscription->date_traitement 
            ? $preinscription->date_traitement->format('d/m/Y')
            : 'N/A';

        $agentName = $preinscription->agent 
            ? $preinscription->agent->name 
            : 'Non assigné';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Détail du Dossier</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .section { margin-bottom: 20px; }
                .section-title { font-weight: bold; background: #f5f5f5; padding: 5px; margin-bottom: 10px; }
                .info-row { display: flex; margin-bottom: 5px; }
                .info-label { font-weight: bold; width: 200px; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>DOSSIER DE PRÉINSCRIPTION</h1>
                <p>Numéro: {$preinscription->numero_dossier}</p>
            </div>
            
            <div class='section'>
                <div class='section-title'>INFORMATIONS PERSONNELLES</div>
                <div class='info-row'><span class='info-label'>Nom:</span> {$preinscription->nom}</div>
                <div class='info-row'><span class='info-label'>Prénom:</span> {$preinscription->prenom}</div>
                <div class='info-row'><span class='info-label'>Email:</span> {$preinscription->email}</div>
                <div class='info-row'><span class='info-label'>Téléphone:</span> {$preinscription->telephone}</div>
                <div class='info-row'><span class='info-label'>Date de naissance:</span> {$preinscription->date_naissance}</div>
            </div>
            
            <div class='section'>
                <div class='section-title'>INFORMATIONS DE PAIEMENT</div>
                " . ($preinscription->paiement ? "
                <div class='info-row'><span class='info-label'>Référence:</span> {$preinscription->paiement->reference_paiement}</div>
                <div class='info-row'><span class='info-label'>Montant:</span> {$preinscription->paiement->montant} FCFA</div>
                <div class='info-row'><span class='info-label'>Statut:</span> {$preinscription->paiement->statut}</div>
                <div class='info-row'><span class='info-label'>Date paiement:</span> {$datePaiement}</div>
                " : "<div class='info-row'>Aucun paiement associé</div>") . "
            </div>
            
            <div class='section'>
                <div class='section-title'>SUIVI ADMINISTRATIF</div>
                <div class='info-row'><span class='info-label'>Statut:</span> {$preinscription->statut}</div>
                <div class='info-row'><span class='info-label'>Agent:</span> {$agentName}</div>
                <div class='info-row'><span class='info-label'>Commentaire:</span> {$preinscription->commentaire_agent}</div>
                <div class='info-row'><span class='info-label'>Date traitement:</span> {$dateTraitement}</div>
            </div>
            
            <div class='footer'>
                <p>Document généré le: " . now()->format('d/m/Y à H:i') . "</p>
                <p>Ce document est généré automatiquement par le système</p>
            </div>
        </body>
        </html>";

        $pdf = Pdf::loadHTML($html);
        return $pdf->download('dossier-' . $preinscription->numero_dossier . '.pdf');
    }
}