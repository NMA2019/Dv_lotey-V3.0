<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;
use App\Services\PaiementService;
use App\Services\MTNPaymentService;
use App\Services\OrangePaymentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service de notifications SendGrid
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        // Service de paiement principal
        $this->app->singleton(PaiementService::class, function ($app) {
            return new PaiementService(
                $app->make(MTNPaymentService::class),
                $app->make(OrangePaymentService::class)
            );
        });

        // Services de paiement spécifiques aux opérateurs
        $this->app->singleton(MTNPaymentService::class, function ($app) {
            return new MTNPaymentService(
                config('services.mtn.api_key'),
                config('services.mtn.api_secret'),
                config('services.mtn.merchant_id')
            );
        });

        $this->app->singleton(OrangePaymentService::class, function ($app) {
            return new OrangePaymentService(
                config('services.orange.client_id'),
                config('services.orange.client_secret'),
                config('services.orange.merchant_key')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuration des logs pour les services de paiement
        if (config('logging.channels.payment')) {
            \Log::channel('payment')->info('Services de paiement initialisés');
        }
    }
}