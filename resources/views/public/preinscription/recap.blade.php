<!-- resources/views/public/preinscription/recap.blade.php -->
@extends('layouts.app')

@section('title', 'Préinscription - Récapitulatif')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Indicateur de progression -->
            <div class="step-progress mb-5">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <div class="step-label">Informations</div>
                </div>
                <div class="step completed">
                    <div class="step-number">2</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step completed">
                    <div class="step-number">3</div>
                    <div class="step-label">Rendez-vous</div>
                </div>
                <div class="step active">
                    <div class="step-number">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <div class="card shadow-lg border-0 fade-in">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Récapitulatif de votre Préinscription</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Vérifiez attentivement les informations ci-dessous avant de soumettre votre préinscription.</strong>
                    </div>

                    <div class="row">
                        <!-- Informations Personnelles -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">Nom</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['nom'] }}</dd>

                                        <dt class="col-sm-4">Prénom</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['prenom'] }}</dd>

                                        <dt class="col-sm-4">Date de naissance</dt>
                                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($data['etape1']['date_naissance'])->format('d/m/Y') }}</dd>

                                        <dt class="col-sm-4">Lieu de naissance</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['lieu_naissance'] }}</dd>

                                        <dt class="col-sm-4">Nationalité</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['nationalite'] }}</dd>

                                        <dt class="col-sm-4">Email</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['email'] }}</dd>

                                        <dt class="col-sm-4">Téléphone</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['telephone'] }}</dd>

                                        <dt class="col-sm-4">Adresse</dt>
                                        <dd class="col-sm-8">{{ $data['etape1']['adresse'] }}, {{ $data['etape1']['ville'] }}, {{ $data['etape1']['pays'] }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Paiement & Rendez-vous -->
                        <div class="col-md-6 mb-4">
                            <!-- Informations de Paiement -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Informations de Paiement</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">Mode</dt>
                                        <dd class="col-sm-8">
                                            @if($data['etape2']['mode_paiement'] == 'mtn')
                                                MTN Mobile Money
                                            @elseif($data['etape2']['mode_paiement'] == 'orange')
                                                Orange Money
                                            @else
                                                Espèces
                                            @endif
                                        </dd>

                                        @if($data['etape2']['reference_paiement'])
                                        <dt class="col-sm-4">Référence</dt>
                                        <dd class="col-sm-8">{{ $data['etape2']['reference_paiement'] }}</dd>
                                        @endif

                                        <dt class="col-sm-4">Montant</dt>
                                        <dd class="col-sm-8">25,000 FCFA</dd>
                                    </dl>
                                </div>
                            </div>

                            <!-- Rendez-vous -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Rendez-vous</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">Date</dt>
                                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($data['etape3']['date_rendez_vous'])->format('d/m/Y') }}</dd>

                                        <dt class="col-sm-4">Heure</dt>
                                        <dd class="col-sm-8">{{ $data['etape3']['heure_rendez_vous'] }}</dd>

                                        <dt class="col-sm-4">Lieu</dt>
                                        <dd class="col-sm-8">Centre de Formation Professionnelle, Douala</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist des documents -->
                    <div class="card border-danger mb-4">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Checklist - Documents à Apporter</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check1">
                                        <label class="form-check-label" for="check1">
                                            Passeport valide (original + copie)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check2">
                                        <label class="form-check-label" for="check2">
                                            2 photos d'identité récentes
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check3">
                                        <label class="form-check-label" for="check3">
                                            Justificatif de paiement
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check4">
                                        <label class="form-check-label" for="check4">
                                            Copies des diplômes
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consentement -->
                    <div class="card border-primary">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Consentement et Validation</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input @error('consentement') is-invalid @enderror" 
                                       type="checkbox" id="consentement" name="consentement" required>
                                <label class="form-check-label" for="consentement">
                                    <strong>Je certifie sur l'honneur l'exactitude des informations fournies et j'accepte les conditions générales d'utilisation.</strong>
                                </label>
                                @error('consentement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="alert alert-warning small mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention :</strong> Toute fausse déclaration entraînera le rejet définitif de votre dossier.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('preinscription.etape3') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Modifier
                        </a>
                        <form action="{{ route('preinscription.store') }}" method="POST" id="finalForm">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Soumettre ma Préinscription
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const finalForm = document.getElementById('finalForm');
    const submitBtn = document.getElementById('submitBtn');
    const consentCheckbox = document.getElementById('consentement');

    finalForm.addEventListener('submit', function(e) {
        if (!consentCheckbox.checked) {
            e.preventDefault();
            alert('Veuillez accepter les conditions en cochant la case de consentement.');
            consentCheckbox.focus();
            return;
        }

        // Afficher le loading
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Soumission en cours...';
        submitBtn.disabled = true;

        // La soumission continue normalement
    });
});
</script>
@endpush
@endsection