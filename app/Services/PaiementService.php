<?php

namespace App\Services;

use App\Models\Preinscription;
use App\Models\Paiement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaiementService
{
    protected $mtnService;
    protected $orangeService;

    public function __construct(MTNPaymentService $mtnService, OrangePaymentService $orangeService)
    {
        $this->mtnService = $mtnService;
        $this->orangeService = $orangeService;
    }

    /**
     * Traiter un paiement mobile money
     */
    public function processMobilePayment(Preinscription $preinscription, string $operator, string $phone, float $amount)
    {
        try {
            DB::beginTransaction();

            // Vérifier si un paiement existe déjà
            $existingPaiement = Paiement::where('preinscription_id', $preinscription->id)
                ->where('statut', 'valide')
                ->first();

            if ($existingPaiement) {
                return [
                    'success' => false,
                    'message' => 'Un paiement a déjà été effectué pour cette préinscription.'
                ];
            }

            // Sélectionner le service approprié
            switch ($operator) {
                case 'mtn':
                    $result = $this->mtnService->initiatePayment($phone, $amount, $preinscription->numero_dossier);
                    break;
                case 'orange':
                    $result = $this->orangeService->initiatePayment($phone, $amount, $preinscription->numero_dossier);
                    break;
                default:
                    throw new \Exception('Opérateur non supporté.');
            }

            if ($result['success']) {
                // Mettre à jour ou créer le paiement
                $paiement = Paiement::updateOrCreate(
                    ['preinscription_id' => $preinscription->id],
                    [
                        'mode_paiement' => $operator,
                        'reference_paiement' => $result['transaction_id'],
                        'montant' => $amount,
                        'statut' => 'en_attente',
                        'telephone' => $phone,
                        'metadata' => $result
                    ]
                );

                DB::commit();

                return [
                    'success' => true,
                    'transaction_id' => $result['transaction_id'],
                    'message' => 'Paiement initié avec succès. Veuillez confirmer sur votre mobile.',
                    'paiement_id' => $paiement->id
                ];
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Erreur lors de l\'initiation du paiement.'
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur traitement paiement mobile: ' . $e->getMessage(), [
                'preinscription_id' => $preinscription->id,
                'operator' => $operator,
                'phone' => $phone
            ]);

            return [
                'success' => false,
                'message' => 'Erreur système lors du traitement du paiement.'
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus(string $transactionId, string $operator)
    {
        try {
            switch ($operator) {
                case 'mtn':
                    return $this->mtnService->checkPaymentStatus($transactionId);
                case 'orange':
                    return $this->orangeService->checkPaymentStatus($transactionId);
                default:
                    return [
                        'success' => false,
                        'message' => 'Opérateur non supporté.'
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Erreur vérification statut paiement: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'operator' => $operator
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut.'
            ];
        }
    }

    /**
     * Traiter un callback de paiement
     */
    public function handlePaymentCallback(array $callbackData, string $operator)
    {
        try {
            DB::beginTransaction();

            $transactionId = $callbackData['transaction_id'] ?? $callbackData['reference'];

            // Trouver le paiement correspondant
            $paiement = Paiement::where('reference_paiement', $transactionId)->first();

            if (!$paiement) {
                Log::warning('Paiement non trouvé pour callback', [
                    'transaction_id' => $transactionId,
                    'operator' => $operator,
                    'callback_data' => $callbackData
                ]);
                return false;
            }

            $status = $this->mapCallbackStatus($callbackData['status'] ?? '', $operator);

            if ($status === 'valide') {
                $paiement->update([
                    'statut' => 'valide',
                    'date_paiement' => now(),
                    'metadata' => array_merge($paiement->metadata ?? [], ['callback_data' => $callbackData])
                ]);

                // Notifier l'utilisateur
                if ($paiement->preinscription) {
                    app(NotificationService::class)->sendPaiementConfirmationNotification($paiement->preinscription);
                }

            } elseif ($status === 'rejete') {
                $paiement->update([
                    'statut' => 'rejete',
                    'metadata' => array_merge($paiement->metadata ?? [], [
                        'callback_data' => $callbackData,
                        'raison_echec' => $callbackData['message'] ?? 'Raison non spécifiée'
                    ])
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur traitement callback paiement: ' . $e->getMessage(), [
                'operator' => $operator,
                'callback_data' => $callbackData
            ]);
            return false;
        }
    }

    /**
     * Mapper les statuts des callbacks
     */
    private function mapCallbackStatus(string $status, string $operator): string
    {
        $statusMap = [
            'mtn' => [
                'SUCCESS' => 'valide',
                'SUCCESSFUL' => 'valide',
                'FAILED' => 'rejete',
                'PENDING' => 'en_attente',
                'EXPIRED' => 'rejete'
            ],
            'orange' => [
                'SUCCESS' => 'valide',
                'SUCCESSFUL' => 'valide',
                'FAILED' => 'rejete',
                'PENDING' => 'en_attente',
                'EXPIRED' => 'rejete',
                'INSUFFICIENT_BALANCE' => 'rejete'
            ]
        ];

        return $statusMap[$operator][strtoupper($status)] ?? 'en_attente';
    }

    /**
     * Annuler un paiement
     */
    public function cancelPayment(string $transactionId, string $operator)
    {
        try {
            switch ($operator) {
                case 'mtn':
                    return $this->mtnService->cancelPayment($transactionId);
                case 'orange':
                    return $this->orangeService->cancelPayment($transactionId);
                default:
                    return [
                        'success' => false,
                        'message' => 'Opérateur non supporté.'
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Erreur annulation paiement: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'operator' => $operator
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du paiement.'
            ];
        }
    }
}