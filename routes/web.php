<?php
// routes/web.php

use App\Http\Controllers\Public\PreinscriptionController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PreinscriptionController as AdminPreinscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Pages publiques
Route::get('/', [PageController::class, 'accueil'])->name('accueil');
Route::get('/bon-a-savoir', [PageController::class, 'bonASavoir'])->name('bon-a-savoir');
Route::get('/tarifs', [PageController::class, 'tarifs'])->name('tarifs');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'envoyerMessage'])->name('contact.send');

// Préinscription
Route::prefix('preinscription')->name('preinscription.')->group(function () {
    Route::get('/etape1', [PreinscriptionController::class, 'etape1'])->name('etape1');
    Route::post('/etape1', [PreinscriptionController::class, 'storeEtape1'])->name('etape1.store');
    Route::get('/etape2', [PreinscriptionController::class, 'etape2'])->name('etape2');
    Route::post('/etape2', [PreinscriptionController::class, 'storeEtape2'])->name('etape2.store');
    Route::get('/etape3', [PreinscriptionController::class, 'etape3'])->name('etape3');
    Route::post('/etape3', [PreinscriptionController::class, 'storeEtape3'])->name('etape3.store');
    Route::get('/recap', [PreinscriptionController::class, 'recap'])->name('recap');
    Route::post('/soumettre', [PreinscriptionController::class, 'store'])->name('store');
    Route::get('/confirmation/{preinscription}', [PreinscriptionController::class, 'confirmation'])->name('confirmation');
});

// Authentification personnalisée (si vous voulez garder votre propre système)
// Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Zone admin/agent
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion des préinscriptions
    Route::get('/preinscriptions', [AdminPreinscriptionController::class, 'index'])->name('preinscriptions.index');
    Route::get('/preinscriptions/{preinscription}', [AdminPreinscriptionController::class, 'show'])->name('preinscriptions.show');
    Route::put('/preinscriptions/{preinscription}', [AdminPreinscriptionController::class, 'update'])->name('preinscriptions.update');

    // Actions supplémentaires pour le traitement
    Route::post('/preinscriptions/{preinscription}/valider', [AdminPreinscriptionController::class, 'valider'])->name('preinscriptions.valider');
    Route::post('/preinscriptions/{preinscription}/rejeter', [AdminPreinscriptionController::class, 'rejeter'])->name('preinscriptions.rejeter');
    Route::post('/preinscriptions/{preinscription}/reclasser', [AdminPreinscriptionController::class, 'reclasser'])->name('preinscriptions.reclasser');
    Route::post('/preinscriptions/{preinscription}/mettre-en-attente', [AdminPreinscriptionController::class, 'mettreEnAttente'])->name('preinscriptions.mettre-en-attente');

    // Gestion des utilisateurs (admin seulement)
    Route::middleware(['admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});

// Routes d'authentification Laravel (au cas où)
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
