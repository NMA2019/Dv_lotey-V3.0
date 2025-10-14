<!-- resources/views/public/preinscription/confirmation.blade.php -->
@extends('layouts.app')

@section('title', 'Préinscription Confirmée')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 fade-in">
                <div class="card-header bg-success text-white text-center">
                    <i class="fas fa-check-circle fa-4x mb-3"></i>
                    <h3 class="mb-0">Préinscription Confirmée!</h3>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-success mb-4">
                        <h5 class="alert-heading">Félicitations {{ $preinscription->prenom }}!</h5>
                        <p class="mb-0">Votre préinscription à la loterie américaine a été enregistrée avec succès.</p>
                    </div>

                    <!-- Numéro de dossier -->
                    <div class="card border-primary mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-primary">Votre numéro de dossier</h6>
                            <div class="display-4 fw-bold text-primary mb-2">{{ $preinscription->numero_dossier }}</div>
                            <p class="text-muted">Conservez précieusement ce numéro pour suivre votre dossier</p>
                        </div>
                    </div>

                    <!-- Récapitulatif -->
                    <div class="row text-start mb-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Informations Personnelles</h6>
                            <p><strong>Nom :</strong> {{ $preinscription->nom }} {{ $preinscription->prenom }}</p>
                            <p><strong>Email :</strong> {{ $preinscription->email }}</p>
                            <p><strong>Téléphone :</strong> {{ $preinscription->telephone }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Rendez-vous</h6>
                            <p><strong>Date :</strong> {{ $preinscription->date_rendez_vous->format('d/m/Y') }}</p>
                            <p><strong>Heure :</strong> {{ $preinscription->heure_rendez_vous }}</p>
                            <p><strong>Lieu :</strong> Centre de Formation Professionnelle, Douala</p>
                        </div>
                    </div>

                    <!-- Prochaines étapes -->
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-list-alt me-2"></i>Prochaines Étapes</h6>
                        </div>
                        <div class="card-body">
                            <ol class="list-group list-group-numbered">
                                <li class="list-group-item border-0">
                                    <strong>Préparez vos documents</strong> - Passeport, photos, justificatifs
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Effectuez le paiement</strong> - Selon le mode choisi
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Présentez-vous au rendez-vous</strong> - Avec tous les documents
                                </li>
                                <li class="list-group-item border-0">
                                    <strong>Suivez votre dossier</strong> - Via votre espace client
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        <button onclick="window.print()" class="btn btn-outline-primary me-2">
                            <i class="fas fa-print me-2"></i>Imprimer cette page
                        </button>
                        <a href="{{ route('accueil') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>

                    <!-- Email confirmation -->
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-envelope me-2"></i>
                        <strong>Un email de confirmation vous a été envoyé à {{ $preinscription->email }}</strong>
                        <br>Vérifiez votre boîte de réception et vos spams.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .navbar, .footer, .btn {
        display: none !important;
    }
    .card {
        border: 2px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
@endsection