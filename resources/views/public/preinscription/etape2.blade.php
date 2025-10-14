<!-- resources/views/public/preinscription/etape2.blade.php -->
@extends('layouts.app')

@section('title', 'Préinscription - Mode de Paiement')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Indicateur de progression -->
            <div class="step-progress mb-5">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <div class="step-label">Informations</div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Rendez-vous</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <div class="card shadow-lg border-0 fade-in">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Étape 2: Mode de Paiement</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('preinscription.etape2.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <h5 class="text-muted mb-3">Choisissez votre mode de paiement</h5>
                            
                            <div class="row g-4">
                                <!-- MTN Mobile Money -->
                                <div class="col-md-6">
                                    <div class="payment-option card h-100 border-2 payment-card">
                                        <div class="card-body text-center">
                                            <div class="payment-icon mb-3">
                                                <i class="fas fa-mobile-alt fa-3x text-warning"></i>
                                            </div>
                                            <h5 class="card-title">MTN Mobile Money</h5>
                                            <p class="card-text text-muted">Paiement sécurisé via MTN Money</p>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mode_paiement" 
                                                       id="mtn" value="mtn" {{ old('mode_paiement') == 'mtn' ? 'checked' : '' }} required>
                                                <label class="form-check-label fw-bold" for="mtn">
                                                    Choisir MTN
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Orange Money -->
                                <div class="col-md-6">
                                    <div class="payment-option card h-100 border-2 payment-card">
                                        <div class="card-body text-center">
                                            <div class="payment-icon mb-3">
                                                <i class="fas fa-mobile fa-3x text-danger"></i>
                                            </div>
                                            <h5 class="card-title">Orange Money</h5>
                                            <p class="card-text text-muted">Paiement sécurisé via Orange Money</p>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mode_paiement" 
                                                       id="orange" value="orange" {{ old('mode_paiement') == 'orange' ? 'checked' : '' }} required>
                                                <label class="form-check-label fw-bold" for="orange">
                                                    Choisir Orange
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paiement en Espèces -->
                                <div class="col-md-6">
                                    <div class="payment-option card h-100 border-2 payment-card">
                                        <div class="card-body text-center">
                                            <div class="payment-icon mb-3">
                                                <i class="fas fa-money-bill-wave fa-3x text-success"></i>
                                            </div>
                                            <h5 class="card-title">Paiement en Espèces</h5>
                                            <p class="card-text text-muted">Paiement direct à notre agence</p>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="mode_paiement" 
                                                       id="espece" value="espece" {{ old('mode_paiement') == 'espece' ? 'checked' : '' }} required>
                                                <label class="form-check-label fw-bold" for="espece">
                                                    Choisir Espèces
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations tarifaires -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Tarif Standard</h6>
                                            <div class="display-6 fw-bold text-primary">25,000 FCFA</div>
                                            <p class="text-muted small">Frais de préinscription inclus</p>
                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Valable pour une inscription complète
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Référence de paiement (conditionnel) -->
                        <div id="reference-field" class="mb-4" style="display: none;">
                            <label for="reference_paiement" class="form-label fw-bold">
                                Référence de Paiement *
                            </label>
                            <input type="text" class="form-control @error('reference_paiement') is-invalid @enderror" 
                                   id="reference_paiement" name="reference_paiement" 
                                   value="{{ old('reference_paiement') }}" 
                                   placeholder="Entrez la référence de votre paiement">
                            @error('reference_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Cette référence vous sera fournie après le paiement mobile money
                            </div>
                        </div>

                        <!-- Instructions de paiement -->
                        <div id="payment-instructions" class="alert alert-info" style="display: none;">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Instructions de paiement</h6>
                            <div id="mtn-instructions" style="display: none;">
                                <p class="mb-2">Pour payer via <strong>MTN Mobile Money</strong> :</p>
                                <ol class="mb-0 small">
                                    <li>Composez <code>*126#</code> sur votre téléphone</li>
                                    <li>Sélectionnez "Paiement de factures"</li>
                                    <li>Entrez le code marchand : <strong>DVLOTERY</strong></li>
                                    <li>Montant : <strong>25,000 FCFA</strong></li>
                                    <li>Notez la référence de transaction</li>
                                </ol>
                            </div>
                            <div id="orange-instructions" style="display: none;">
                                <p class="mb-2">Pour payer via <strong>Orange Money</strong> :</p>
                                <ol class="mb-0 small">
                                    <li>Composez <code>#144#</code> sur votre téléphone</li>
                                    <li>Sélectionnez "Payer une facture"</li>
                                    <li>Entrez le code marchand : <strong>DVLOTERY</strong></li>
                                    <li>Montant : <strong>25,000 FCFA</strong></li>
                                    <li>Notez la référence de transaction</li>
                                </ol>
                            </div>
                            <div id="espece-instructions" style="display: none;">
                                <p class="mb-0">Présentez-vous à notre agence avec une pièce d'identité et le montant de <strong>25,000 FCFA</strong> en espèces.</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('preinscription.etape1') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Suivant <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('input[name="mode_paiement"]');
    const referenceField = document.getElementById('reference-field');
    const paymentInstructions = document.getElementById('payment-instructions');
    const mtnInstructions = document.getElementById('mtn-instructions');
    const orangeInstructions = document.getElementById('orange-instructions');
    const especeInstructions = document.getElementById('espece-instructions');
    const referenceInput = document.getElementById('reference_paiement');

    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Reset all instructions
            [mtnInstructions, orangeInstructions, especeInstructions].forEach(inst => {
                inst.style.display = 'none';
            });

            // Show/hide reference field
            if (this.value === 'mtn' || this.value === 'orange') {
                referenceField.style.display = 'block';
                referenceInput.setAttribute('required', 'required');
            } else {
                referenceField.style.display = 'none';
                referenceInput.removeAttribute('required');
            }

            // Show relevant instructions
            paymentInstructions.style.display = 'block';
            switch(this.value) {
                case 'mtn':
                    mtnInstructions.style.display = 'block';
                    break;
                case 'orange':
                    orangeInstructions.style.display = 'block';
                    break;
                case 'espece':
                    especeInstructions.style.display = 'block';
                    break;
            }

            // Add active class to selected payment card
            document.querySelectorAll('.payment-card').forEach(card => {
                card.classList.remove('border-primary', 'bg-light');
            });
            this.closest('.payment-card').classList.add('border-primary', 'bg-light');
        });
    });

    // Trigger change event for pre-selected option
    const selectedOption = document.querySelector('input[name="mode_paiement"]:checked');
    if (selectedOption) {
        selectedOption.dispatchEvent(new Event('change'));
    }
});
</script>
<style>
.payment-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.payment-card.border-primary {
    border-color: #1e3c72 !important;
    background-color: rgba(30, 60, 114, 0.05) !important;
}

.payment-icon {
    transition: transform 0.3s ease;
}

.payment-card:hover .payment-icon {
    transform: scale(1.1);
}
</style>
@endpush
@endsection