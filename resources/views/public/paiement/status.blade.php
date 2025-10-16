@extends('layouts.public')

@section('title', 'Statut du Paiement')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-spinner me-2"></i>
                        Statut du Paiement
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div id="paymentStatus">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <h5>Vérification du paiement en cours...</h5>
                        <p class="text-muted">Veuillez patienter pendant que nous vérifions le statut de votre transaction.</p>
                    </div>

                    <div id="paymentResult" style="display: none;">
                        <!-- Le résultat sera affiché ici par JavaScript -->
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('preinscription.confirmation', $preinscription) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour au dossier
                        </a>
                        <a href="{{ route('payment.form', $preinscription) }}" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>
                            Nouvelle tentative
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkPaymentStatus = () => {
        fetch('{{ route("payment.check.status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                transaction_id: '{{ $paiement->reference_paiement }}',
                operator: '{{ $paiement->mode_paiement }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            const statusElement = document.getElementById('paymentStatus');
            const resultElement = document.getElementById('paymentResult');
            
            if (data.success) {
                statusElement.style.display = 'none';
                resultElement.style.display = 'block';
                
                let icon, color, message;
                
                switch(data.status) {
                    case 'SUCCESSFUL':
                    case 'SUCCESS':
                        icon = 'fas fa-check-circle';
                        color = 'success';
                        message = '<div class="alert alert-success"><h4><i class="' + icon + ' me-2"></i>Paiement Réussi!</h4><p>Votre paiement a été confirmé. Votre dossier est maintenant complet.</p></div>';
                        break;
                    case 'PENDING':
                        icon = 'fas fa-clock';
                        color = 'warning';
                        message = '<div class="alert alert-warning"><h4><i class="' + icon + ' me-2"></i>En Attente</h4><p>Votre paiement est en cours de traitement. Veuillez patienter.</p></div>';
                        // Continuer à vérifier
                        setTimeout(checkPaymentStatus, 5000);
                        break;
                    case 'FAILED':
                    case 'EXPIRED':
                        icon = 'fas fa-times-circle';
                        color = 'danger';
                        message = '<div class="alert alert-danger"><h4><i class="' + icon + ' me-2"></i>Paiement Échoué</h4><p>' + (data.message || 'Votre paiement n\'a pas pu être traité.') + '</p></div>';
                        break;
                    default:
                        icon = 'fas fa-question-circle';
                        color = 'info';
                        message = '<div class="alert alert-info"><h4><i class="' + icon + ' me-2"></i>Statut Inconnu</h4><p>Impossible de déterminer le statut du paiement.</p></div>';
                }
                
                resultElement.innerHTML = message;
            } else {
                statusElement.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Erreur</h5>
                        <p>${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('paymentStatus').innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Erreur de connexion</h5>
                    <p>Impossible de vérifier le statut. Veuillez rafraîchir la page.</p>
                </div>
            `;
        });
    };

    // Démarrer la vérification
    checkPaymentStatus();
});
</script>
@endpush