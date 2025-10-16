<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OrangePaymentService
{
    private $clientId;
    private $clientSecret;
    private $merchantKey;
    private $environment;
    private $baseUrl;

    public function __construct(string $clientId, string $clientSecret, string $merchantKey, string $environment = 'sandbox')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->merchantKey = $merchantKey;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production' 
            ? 'https://api.orange.com/orange-money-api/v1'
            : 'https://api-sandbox.orange.com/orange-money-api/v1';
    }

    /**
     * Obtenir le token d'accès
     */
    private function getAccessToken()
    {
        return Cache::remember('orange_access_token', 3500, function () {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->asForm()->post('https://api.orange.com/oauth/v2/token', [
                    'grant_type' => 'client_credentials'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'];
                }

                Log::error('Erreur obtention token Orange', $response->json());
                throw new \Exception('Impossible d\'obtenir le token d\'accès Orange');

            } catch (\Exception $e) {
                Log::error('Exception obtention token Orange: ' . $e->getMessage());
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
                'merchant_key' => $this->merchantKey,
                'currency' => 'XAF',
                'order_id' => $reference,
                'amount' => $amount,
                'return_url' => route('payment.orange.callback'),
                'cancel_url' => route('payment.orange.cancel'),
                'notif_url' => route('payment.orange.webhook'),
                'lang' => 'fr',
                'reference' => $reference
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payment', $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['payment_url'])) {
                Log::info('Paiement Orange initié avec succès', [
                    'reference' => $reference,
                    'phone' => $phone,
                    'amount' => $amount,
                    'payment_url' => $responseData['payment_url']
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $responseData['pay_token'],
                    'payment_url' => $responseData['payment_url'],
                    'message' => 'Paiement initié avec succès'
                ];
            } else {
                Log::error('Erreur initiation paiement Orange', [
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
            Log::error('Exception initiation paiement Orange: ' . $e->getMessage(), [
                'phone' => $phone,
                'amount' => $amount,
                'reference' => $reference
            ]);

            return [
                'success' => false,
                'message' => 'Erreur système lors de l\'initiation du paiement Orange'
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
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . "/payment/{$transactionId}/status");

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'message' => $this->getStatusMessage($data['status'])
                ];
            } else {
                Log::error('Erreur vérification statut Orange', $response->json());
                return [
                    'success' => false,
                    'message' => 'Impossible de vérifier le statut du paiement'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception vérification statut Orange: ' . $e->getMessage(), [
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
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . "/payment/{$transactionId}/cancel");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Paiement annulé avec succès'
                ];
            } else {
                Log::error('Erreur annulation paiement Orange', $response->json());
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'annulation du paiement'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception annulation paiement Orange: ' . $e->getMessage(), [
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur système lors de l\'annulation'
            ];
        }
    }

    /**
     * Traiter un webhook Orange
     */
    public function handleWebhook(array $webhookData)
    {
        try {
            // Vérifier la signature
            if (!$this->verifyWebhookSignature($webhookData)) {
                Log::warning('Signature webhook Orange invalide', $webhookData);
                return false;
            }

            $status = $webhookData['status'] ?? '';
            $transactionId = $webhookData['txnid'] ?? '';

            return [
                'transaction_id' => $transactionId,
                'status' => $status,
                'amount' => $webhookData['amount'] ?? 0,
                'currency' => $webhookData['currency'] ?? 'XAF',
                'message' => $webhookData['message'] ?? ''
            ];

        } catch (\Exception $e) {
            Log::error('Exception traitement webhook Orange: ' . $e->getMessage(), $webhookData);
            return false;
        }
    }

    /**
     * Vérifier la signature du webhook
     */
    private function verifyWebhookSignature(array $data): bool
    {
        $signature = $data['hash'] ?? '';
        $expectedSignature = hash_hmac('sha256', 
            $data['txnid'] . $data['status'] . $data['amount'] . $data['currency'],
            $this->merchantKey
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Obtenir un message d'erreur lisible
     */
    private function getErrorMessage(array $response): string
    {
        $errorCode = $response['code'] ?? 'UNKNOWN_ERROR';
        
        $errorMessages = [
            'INVALID_MSISDN' => 'Numéro de téléphone invalide',
            'INSUFFICIENT_BALANCE' => 'Solde insuffisant sur le compte',
            'TRANSACTION_LIMIT_EXCEEDED' => 'Limite de transaction atteinte',
            'INTERNAL_ERROR' => 'Erreur interne du système de paiement',
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
            'SUCCESS' => 'Paiement effectué avec succès',
            'FAILED' => 'Paiement échoué',
            'PENDING' => 'Paiement en attente de confirmation',
            'EXPIRED' => 'Paiement expiré'
        ];

        return $statusMessages[$status] ?? 'Statut inconnu';
    }
}