<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MTNPaymentService
{
    private $apiKey;
    private $apiSecret;
    private $merchantId;
    private $environment;
    private $baseUrl;

    public function __construct(string $apiKey, string $apiSecret, string $merchantId, string $environment = 'sandbox')
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->merchantId = $merchantId;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production' 
            ? 'https://api.mtn.com/v1'
            : 'https://sandbox.mtn.com/v1';
    }

    /**
     * Obtenir le token d'accès
     */
    private function getAccessToken()
    {
        return Cache::remember('mtn_access_token', 3500, function () {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
                    'Content-Type' => 'application/json'
                ])->post($this->baseUrl . '/auth/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'];
                }

                Log::error('Erreur obtention token MTN', $response->json());
                throw new \Exception('Impossible d\'obtenir le token d\'accès MTN');

            } catch (\Exception $e) {
                Log::error('Exception obtention token MTN: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Initier un paiement
     */
    public function initiatePayment(string $phone, float $amount, string $reference)
    {
        try {
            $token = $this->getAccessToken();

            $payload = [
                'amount' => (string) $amount,
                'currency' => 'XAF',
                'externalId' => $reference,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $this->formatPhoneNumber($phone)
                ],
                'payerMessage' => 'Paiement préinscription ' . $reference,
                'payeeNote' => 'Paiement pour dossier ' . $reference
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Reference-Id' => uniqid(),
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $this->apiKey
            ])->post($this->baseUrl . '/collection/v1_0/requesttopay', $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Paiement MTN initié avec succès', [
                    'reference' => $reference,
                    'phone' => $phone,
                    'amount' => $amount,
                    'transaction_id' => $response->header('X-Reference-Id')
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $response->header('X-Reference-Id'),
                    'message' => 'Paiement initié avec succès'
                ];
            } else {
                Log::error('Erreur initiation paiement MTN', [
                    'response' => $responseData,
                    'status' => $response->status(),
                    'payload' => $payload
                ]);

                return [
                    'success' => false,
                    'message' => $this->getErrorMessage($responseData),
                    'error_code' => $responseData['code'] ?? 'UNKNOWN_ERROR'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception initiation paiement MTN: ' . $e->getMessage(), [
                'phone' => $phone,
                'amount' => $amount,
                'reference' => $reference
            ]);

            return [
                'success' => false,
                'message' => 'Erreur système lors de l\'initiation du paiement MTN'
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus(string $transactionId)
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $this->apiKey
            ])->get($this->baseUrl . "/collection/v1_0/requesttopay/{$transactionId}");

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'financialTransactionId' => $data['financialTransactionId'] ?? null,
                    'message' => $this->getStatusMessage($data['status'])
                ];
            } else {
                Log::error('Erreur vérification statut MTN', $response->json());
                return [
                    'success' => false,
                    'message' => 'Impossible de vérifier le statut du paiement'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception vérification statut MTN: ' . $e->getMessage(), [
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut'
            ];
        }
    }

    /**
     * Annuler un paiement
     */
    public function cancelPayment(string $transactionId)
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $this->apiKey
            ])->post($this->baseUrl . "/collection/v1_0/requesttopay/{$transactionId}/cancel");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Paiement annulé avec succès'
                ];
            } else {
                Log::error('Erreur annulation paiement MTN', $response->json());
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'annulation du paiement'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception annulation paiement MTN: ' . $e->getMessage(), [
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur système lors de l\'annulation'
            ];
        }
    }

    /**
     * Formater le numéro de téléphone
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Supprimer les espaces et le préfixe
        $phone = preg_replace('/\s+/', '', $phone);
        
        // Ajouter l'indicatif camerounais si nécessaire
        if (strpos($phone, '237') === 0) {
            return $phone;
        }
        
        if (strpos($phone, '0') === 0) {
            return '237' . substr($phone, 1);
        }
        
        return '237' . $phone;
    }

    /**
     * Obtenir un message d'erreur lisible
     */
    private function getErrorMessage(array $response): string
    {
        $errorCode = $response['code'] ?? 'UNKNOWN_ERROR';
        
        $errorMessages = [
            'PAYER_NOT_FOUND' => 'Numéro de téléphone invalide ou non trouvé',
            'NOT_ENOUGH_FUNDS' => 'Solde insuffisant sur le compte',
            'PAYER_LIMIT_REACHED' => 'Limite de transaction atteinte',
            'INTERNAL_PROCESSING_ERROR' => 'Erreur interne du système de paiement',
            'TRANSACTION_CANCELED' => 'Transaction annulée par l\'utilisateur',
            'TRANSACTION_FAILED' => 'Échec de la transaction',
            'UNKNOWN_ERROR' => 'Erreur inconnue, veuillez réessayer'
        ];

        return $errorMessages[$errorCode] ?? $errorMessages['UNKNOWN_ERROR'];
    }

    /**
     * Obtenir un message de statut
     */
    private function getStatusMessage(string $status): string
    {
        $statusMessages = [
            'SUCCESSFUL' => 'Paiement effectué avec succès',
            'FAILED' => 'Paiement échoué',
            'PENDING' => 'Paiement en attente de confirmation',
            'EXPIRED' => 'Paiement expiré'
        ];

        return $statusMessages[$status] ?? 'Statut inconnu';
    }
}