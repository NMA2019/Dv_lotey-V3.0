<?php
// routes/web.php

use App\Http\Controllers\Public\PreinscriptionController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PreinscriptionController as AdminPreinscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PdfController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/

// Pages publiques
Route::get('/', [PageController::class, 'accueil'])->name('accueil');
Route::get('/bon-a-savoir', [PageController::class, 'bonASavoir'])->name('bon-a-savoir');
Route::get('/tarifs', [PageController::class, 'tarifs'])->name('tarifs');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'envoyerMessage'])->name('contact.send');

// Vérification statut préinscription
Route::get('/verification-statut', [PreinscriptionController::class, 'checkStatusPage'])->name('verification.statut');
Route::post('/verification-statut', [PreinscriptionController::class, 'checkStatus'])->name('verification.statut.check');

/*
|--------------------------------------------------------------------------
| Processus de Préinscription
|--------------------------------------------------------------------------
*/

Route::prefix('preinscription')->name('preinscription.')->group(function () {
    // Étape 1 - Informations personnelles
    Route::get('/etape1', [PreinscriptionController::class, 'etape1'])->name('etape1');
    Route::post('/etape1', [PreinscriptionController::class, 'storeEtape1'])->name('etape1.store');
    
    // Étape 2 - Mode de paiement
    Route::get('/etape2', [PreinscriptionController::class, 'etape2'])->name('etape2');
    Route::post('/etape2', [PreinscriptionController::class, 'storeEtape2'])->name('etape2.store');
    
    // Étape 3 - Rendez-vous
    Route::get('/etape3', [PreinscriptionController::class, 'etape3'])->name('etape3');
    Route::post('/etape3', [PreinscriptionController::class, 'storeEtape3'])->name('etape3.store');
    
    // Récapitulatif et soumission
    Route::get('/recap', [PreinscriptionController::class, 'recap'])->name('recap');
    Route::post('/soumettre', [PreinscriptionController::class, 'store'])->name('store');
    
    // Confirmation
    Route::get('/confirmation/{preinscription}', [PreinscriptionController::class, 'confirmation'])->name('confirmation');
    
    // Téléchargement reçu
    Route::get('/{preinscription}/receipt', [PreinscriptionController::class, 'downloadReceipt'])->name('receipt');
});

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/

// Routes d'authentification Laravel (désactiver l'inscription)
Auth::routes(['register' => false]);

// Routes d'authentification personnalisées (alternative)
Route::middleware('guest')->group(function () {
    Route::get('/login-custom', [AuthController::class, 'showLoginForm'])->name('login.custom');
    Route::post('/login-custom', [AuthController::class, 'login']);
});

Route::post('/logout-custom', [AuthController::class, 'logout'])->name('logout.custom');

/*
|--------------------------------------------------------------------------
| Zone Administrateur/Agent
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API Statistiques
    Route::get('/stats', [DashboardController::class, 'getStatsApi'])->name('stats.api');

    /*
    |--------------------------------------------------------------------------
    | Gestion des Préinscriptions
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('preinscriptions')->name('preinscriptions.')->group(function () {
        // Liste et affichage
        Route::get('/', [AdminPreinscriptionController::class, 'index'])->name('index');
        Route::get('/{preinscription}', [AdminPreinscriptionController::class, 'show'])->name('show');
        Route::put('/{preinscription}', [AdminPreinscriptionController::class, 'update'])->name('update');
        
        // Actions de traitement
        Route::post('/{preinscription}/valider', [AdminPreinscriptionController::class, 'valider'])->name('valider');
        Route::post('/{preinscription}/rejeter', [AdminPreinscriptionController::class, 'rejeter'])->name('rejeter');
        Route::post('/{preinscription}/reclasser', [AdminPreinscriptionController::class, 'reclasser'])->name('reclasser');
        Route::post('/{preinscription}/mettre-en-attente', [AdminPreinscriptionController::class, 'mettreEnAttente'])->name('mettre-en-attente');
        
        // Export Excel
        Route::get('/export/excel', [AdminPreinscriptionController::class, 'exporter'])->name('export.excel');
    });

    /*
    |--------------------------------------------------------------------------
    | Génération de PDF
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('pdf')->name('pdf.')->group(function () {
        // Reçu de préinscription
        Route::get('/preinscription/{preinscription}/receipt', [PdfController::class, 'preinscriptionReceipt'])->name('preinscription.receipt');
        
        // Détail complet
        Route::get('/preinscription/{preinscription}/detail', [PdfController::class, 'preinscriptionDetail'])->name('preinscription.detail');
        
        // Liste des préinscriptions
        Route::get('/preinscriptions/export', [PdfController::class, 'exportPreinscriptions'])->name('preinscriptions.export');
        
        // Rapport des paiements
        Route::get('/paiements/report', [PdfController::class, 'paiementsReport'])->name('paiements.report');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestion des Utilisateurs (Admin uniquement)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['admin'])->prefix('users')->name('users.')->group(function () {
        // CRUD Utilisateurs
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        
        // Actions supplémentaires
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestion des Paiements
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('paiements')->name('paiements.')->group(function () {
        Route::get('/', [AdminPreinscriptionController::class, 'paiements'])->name('index');
        Route::post('/{paiement}/valider', [AdminPreinscriptionController::class, 'validerPaiement'])->name('valider');
        Route::post('/{paiement}/rejeter', [AdminPreinscriptionController::class, 'rejeterPaiement'])->name('rejeter');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestion du Calendrier
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('calendrier')->name('calendrier.')->group(function () {
        Route::get('/', [AdminPreinscriptionController::class, 'calendrier'])->name('index');
        Route::post('/creneaux/generer', [AdminPreinscriptionController::class, 'genererCreneaux'])->name('creneaux.generer');
        Route::put('/creneaux/{creneau}', [AdminPreinscriptionController::class, 'updateCreneau'])->name('creneaux.update');
    });
});

/*
|--------------------------------------------------------------------------
| Routes de Secours et Redirections
|--------------------------------------------------------------------------
*/

// Redirection /home vers dashboard admin
Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

// Route de fallback pour les pages 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

/*
|--------------------------------------------------------------------------
| Routes API (si nécessaire)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    Route::get('/preinscription/status', [PreinscriptionController::class, 'checkStatus']);
    Route::get('/calendrier/creneaux', [PreinscriptionController::class, 'getCreneauxDisponibles']);
});