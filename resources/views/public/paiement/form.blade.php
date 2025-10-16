@extends('layouts.public')

@section('title', 'Paiement de la Préinscription')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Paiement de la Préinscription
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>Dossier: {{ $preinscription->numero_dossier }}</h5>
                        <p class="mb-0"><strong>Montant à payer:</strong> <span class="text-success fw-bold">5 000 FCFA</span></p>
                    </div>

                    <form method="POST" action="{{ route('payment.process', $preinscription) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="operator" class="form-label">Opérateur Mobile Money *</label>
                            <select class="form-select @error('operator') is-invalid @enderror" id="operator" name="operator" required>
                                <option value="">Choisissez votre opérateur</option>
                                <option value="mtn" {{ old('operator') == 'mtn' ? 'selected' : '' }}>MTN Mobile Money</option>
                                <option value="orange" {{ old('operator') == 'orange' ? 'selected' : '' }}>Orange Money</option>
                            </select>
                            @error('operator')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Numéro de téléphone *</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}" 
                                   placeholder="Ex: 6XX XXX XXX" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Le numéro doit être associé à votre compte Mobile Money</div>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle me-2"></i>Instructions</h6>
                            <ul class="mb-0 small">
                                <li>Assurez-vous d'avoir suffisamment de crédit sur votre compte</li>
                                <li>Vous recevrez une demande de confirmation sur votre mobile</li>
                                <li>Validez la transaction lorsque vous recevez la notification</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Payer 5 000 FCFA
                            </button>
                            <a href="{{ route('preinscription.confirmation', $preinscription) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection