<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PaiementService;
use App\Models\Paiement;
use App\Models\Preinscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paiementService;

    public function __construct(PaiementService $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    /**
     * Afficher le formulaire de paiement
     */
    public function showPaymentForm(Preinscription $preinscription)
    {
        // Vérifier que la préinscription peut être payée
        if ($preinscription->statut !== 'en_attente') {
            return redirect()->route('preinscription.confirmation', $preinscription)
                ->with('error', 'Cette préinscription ne peut pas être payée.');
        }

        return view('public.paiement.form', compact('preinscription'));
    }

    /**
     * Traiter le paiement
     */
    public function processPayment(Request $request, Preinscription $preinscription)
    {
        $request->validate([
            'operator' => 'required|in:mtn,orange',
            'phone' => 'required|string|max:20'
        ]);

        $result = $this->paiementService->processMobilePayment(
            $preinscription,
            $request->operator,
            $request->phone,
            5000 // Montant fixe pour la préinscription
        );

        if ($result['success']) {
            return redirect()->route('payment.status', [
                'preinscription' => $preinscription,
                'transaction_id' => $result['transaction_id']
            ])->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }

    /**
     * Afficher le statut du paiement
     */
    public function paymentStatus(Preinscription $preinscription, string $transactionId)
    {
        $paiement = Paiement::where('preinscription_id', $preinscription->id)
            ->where('reference_paiement', $transactionId)
            ->firstOrFail();

        return view('public.paiement.status', compact('preinscription', 'paiement'));
    }

    /**
     * Vérifier le statut du paiement (AJAX)
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'operator' => 'required|in:mtn,orange'
        ]);

        $result = $this->paiementService->checkPaymentStatus(
            $request->transaction_id,
            $request->operator
        );

        return response()->json($result);
    }

    /**
     * Webhook MTN
     */
    public function handleMtnWebhook(Request $request)
    {
        Log::info('Webhook MTN reçu', $request->all());

        $result = $this->paiementService->handlePaymentCallback($request->all(), 'mtn');

        return response()->json(['success' => $result]);
    }

    /**
     * Webhook Orange
     */
    public function handleOrangeWebhook(Request $request)
    {
        Log::info('Webhook Orange reçu', $request->all());

        $result = $this->paiementService->handlePaymentCallback($request->all(), 'orange');

        return response()->json(['success' => $result]);
    }

    /**
     * Callback de retour Orange
     */
    public function handleOrangeCallback(Request $request)
    {
        $status = $request->get('status', 'unknown');
        $transactionId = $request->get('txnid', '');

        Log::info('Callback Orange reçu', [
            'status' => $status,
            'transaction_id' => $transactionId,
            'all_data' => $request->all()
        ]);

        // Rediriger vers la page de statut
        if ($transactionId) {
            $paiement = Paiement::where('reference_paiement', $transactionId)->first();
            if ($paiement) {
                return redirect()->route('payment.status', [
                    'preinscription' => $paiement->preinscription_id,
                    'transaction_id' => $transactionId
                ]);
            }
        }

        return redirect()->route('home')
            ->with('error', 'Impossible de retrouver la transaction.');
    }

    /**
     * Annuler un paiement
     */
    public function cancelPayment(Request $request, Paiement $paiement)
    {
        if ($paiement->statut !== 'en_attente') {
            return redirect()->back()
                ->with('error', 'Impossible d\'annuler ce paiement.');
        }

        $result = $this->paiementService->cancelPayment(
            $paiement->reference_paiement,
            $paiement->mode_paiement
        );

        if ($result['success']) {
            $paiement->update(['statut' => 'rejete']);
            return redirect()->back()
                ->with('success', 'Paiement annulé avec succès.');
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }
}