<!-- resources/views/public/tarifs.blade.php -->
@extends('layouts.app')

@section('title', 'Tarifs et Forfaits')
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">Nos Tarifs</h1>
            <p class="lead">Choisissez la formule qui correspond le mieux à vos besoins</p>
        </div>
    </div>

    <div class="row justify-content-center">
        @foreach($tarifs as $tarif)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card pricing-card h-100 border-0 shadow-lg {{ $tarif['recommandé'] ? 'border-primary' : '' }}">
                @if($tarif['recommandé'])
                <div class="card-header bg-primary text-white text-center py-3">
                    <span class="badge bg-warning text-dark fs-6">LE PLUS POPULAIRE</span>
                </div>
                @endif
                <div class="card-body text-center p-4">
                    <h5 class="card-title fw-bold text-primary">{{ $tarif['nom'] }}</h5>
                    <div class="price display-4 fw-bold text-dark my-4">
                        {{ number_format($tarif['prix'], 0, ',', ' ') }}
                        <small class="fs-6 text-muted">{{ $tarif['devise'] }}</small>
                    </div>
                    
                    <ul class="list-unstyled mb-4">
                        @foreach($tarif['avantages'] as $avantage)
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            {{ $avantage }}
                        </li>
                        @endforeach
                    </ul>
                    
                    <div class="mt-auto">
                        <a href="{{ route('preinscription.etape1') }}" 
                           class="btn btn-lg w-100 {{ $tarif['recommandé'] ? 'btn-primary' : 'btn-outline-primary' }}">
                            Choisir cette offre
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Informations complémentaires -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 bg-light">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">Informations Complémentaires</h4>
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3">
                                <i class="fas fa-shield-alt fa-2x text-primary mb-3"></i>
                                <h6>Paiement Sécurisé</h6>
                                <p class="small text-muted mb-0">Tous les paiements sont cryptés et sécurisés</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3">
                                <i class="fas fa-undo-alt fa-2x text-primary mb-3"></i>
                                <h6>Remboursement</h6>
                                <p class="small text-muted mb-0">Politique de remboursement sous 14 jours</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3">
                                <i class="fas fa-headset fa-2x text-primary mb-3"></i>
                                <h6>Support Client</h6>
                                <p class="small text-muted mb-0">Assistance disponible 6j/7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.pricing-card {
    transition: all 0.3s ease;
    border: 3px solid transparent;
}

.pricing-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}

.pricing-card.border-primary {
    border-color: #1e3c72 !important;
}
</style>
@endpush