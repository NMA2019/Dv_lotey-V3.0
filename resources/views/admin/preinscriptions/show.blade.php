@extends('layouts.app')

@section('title', 'Détail Préinscription - ' . $preinscription->numero_dossier)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Dossier: {{ $preinscription->numero_dossier }}
                        </h5>
                        <small class="opacity-75">Créé le {{ $preinscription->created_at->format('d/m/Y à H:i') }}</small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.pdf.preinscription.detail', $preinscription) }}" 
                           class="btn btn-light btn-sm" target="_blank">
                            <i class="fas fa-print me-1"></i>Imprimer
                        </a>
                        <a href="{{ route('admin.preinscriptions.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Informations principales -->
                <div class="col-lg-8">
                    <!-- Informations personnelles -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                Informations Personnelles
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold" style="width: 40%">Nom:</td>
                                            <td>{{ $preinscription->nom }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Prénom:</td>
                                            <td>{{ $preinscription->prenom }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Date de naissance:</td>
                                            <td>{{ $preinscription->date_naissance->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Lieu de naissance:</td>
                                            <td>{{ $preinscription->lieu_naissance }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold" style="width: 40%">Nationalité:</td>
                                            <td>{{ $preinscription->nationalite }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Ville:</td>
                                            <td>{{ $preinscription->ville }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Pays:</td>
                                            <td>{{ $preinscription->pays }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Adresse:</td>
                                            <td>{{ $preinscription->adresse }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coordonnées -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-address-card me-2"></i>
                                Coordonnées
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start mb-3">
                                        <i class="fas fa-envelope text-primary me-3 mt-1"></i>
                                        <div>
                                            <strong>Email</strong>
                                            <div>{{ $preinscription->email }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start mb-3">
                                        <i class="fas fa-phone text-primary me-3 mt-1"></i>
                                        <div>
                                            <strong>Téléphone</strong>
                                            <div>{{ $preinscription->telephone }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rendez-vous -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                Rendez-vous
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start mb-3">
                                        <i class="fas fa-calendar-day text-warning me-3 mt-1"></i>
                                        <div>
                                            <strong>Date</strong>
                                            <div>{{ $preinscription->date_rendez_vous->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start mb-3">
                                        <i class="fas fa-clock text-warning me-3 mt-1"></i>
                                        <div>
                                            <strong>Heure</strong>
                                            <div>{{ $preinscription->heure_rendez_vous }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <strong>Lieu:</strong> Centre de Formation Professionnelle, Douala
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Actions et statut -->
                <div class="col-lg-4">
                    <!-- Statut du dossier -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>
                                Statut du Dossier
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @php
                                    $statutClasses = [
                                        'valide' => 'success',
                                        'en_attente' => 'warning', 
                                        'rejete' => 'danger',
                                        'reclasse' => 'info'
                                    ];
                                    $statutLabels = [
                                        'valide' => 'Validé',
                                        'en_attente' => 'En Attente',
                                        'rejete' => 'Rejeté',
                                        'reclasse' => 'Reclassé'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statutClasses[$preinscription->statut] ?? 'secondary' }} fs-6 py-2 px-3">
                                    {{ $statutLabels[$preinscription->statut] ?? $preinscription->statut }}
                                </span>
                            </div>

                            @if($preinscription->agent)
                            <div class="mb-3">
                                <small class="text-muted">Traité par</small>
                                <div class="fw-bold">{{ $preinscription->agent->name }}</div>
                                <small class="text-muted">le {{ $preinscription->updated_at->format('d/m/Y') }}</small>
                            </div>
                            @endif

                            @if($preinscription->commentaire_agent)
                            <div class="alert alert-info">
                                <strong>Commentaire:</strong>
                                <p class="mb-0 mt-1">{{ $preinscription->commentaire_agent }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Actions Rapides
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($preinscription->statut === 'en_attente')
                            <div class="d-grid gap-2">
                                <button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#validerModal">
                                    <i class="fas fa-check me-1"></i>Valider le dossier
                                </button>
                                <button class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#rejeterModal">
                                    <i class="fas fa-times me-1"></i>Rejeter le dossier
                                </button>
                                <button class="btn btn-warning mb-2" data-bs-toggle="modal" data-bs-target="#reclasserModal">
                                    <i class="fas fa-exchange-alt me-1"></i>Reclasser
                                </button>
                            </div>
                            @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                Ce dossier a déjà été traité
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#mettreAttenteModal">
                                    <i class="fas fa-clock me-1"></i>Remettre en attente
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informations de paiement -->
                    @if($preinscription->paiement)
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-money-bill me-2"></i>
                                Paiement
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold">Mode:</td>
                                    <td>{{ $preinscription->paiement->mode_paiement }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Référence:</td>
                                    <td>{{ $preinscription->paiement->reference_paiement ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Montant:</td>
                                    <td class="fw-bold text-success">{{ number_format($preinscription->paiement->montant, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Statut:</td>
                                    <td>
                                        @php
                                            $paiementStatutClasses = [
                                                'valide' => 'success',
                                                'en_attente' => 'warning',
                                                'rejete' => 'danger'
                                            ];
                                            $paiementStatutLabels = [
                                                'valide' => 'Validé',
                                                'en_attente' => 'En Attente',
                                                'rejete' => 'Rejeté'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $paiementStatutClasses[$preinscription->paiement->statut] ?? 'secondary' }}">
                                            {{ $paiementStatutLabels[$preinscription->paiement->statut] ?? $preinscription->paiement->statut }}
                                        </span>
                                    </td>
                                </tr>
                                @if($preinscription->paiement->date_paiement)
                                <tr>
                                    <td class="fw-bold">Date paiement:</td>
                                    <td>{{ $preinscription->paiement->date_paiement->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>

                            @if($preinscription->paiement->statut === 'en_attente')
                            <div class="d-grid gap-2 mt-3">
                                <form method="POST" action="{{ route('admin.paiements.valider', $preinscription->paiement) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100 mb-2">
                                        <i class="fas fa-check me-1"></i>Valider le paiement
                                    </button>
                                </form>
                                <button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#rejeterPaiementModal">
                                    <i class="fas fa-times me-1"></i>Rejeter le paiement
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations de paiement détaillées -->
            @if($preinscription->paiement)
            <div class="card shadow mb-4 mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Informations de Paiement
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%">Mode:</td>
                                    <td>
                                        <span class="badge bg-info">{{ $preinscription->paiement->mode_paiement }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Référence:</td>
                                    <td>{{ $preinscription->paiement->reference_paiement ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Montant:</td>
                                    <td class="fw-bold text-success">{{ number_format($preinscription->paiement->montant, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%">Statut:</td>
                                    <td>
                                        <span class="badge bg-{{ $preinscription->paiement->statut == 'valide' ? 'success' : ($preinscription->paiement->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                            {{ $preinscription->paiement->statut === 'valide' ? 'Validé' : ($preinscription->paiement->statut === 'en_attente' ? 'En Attente' : 'Rejeté') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date paiement:</td>
                                    <td>{{ $preinscription->paiement->date_paiement ? $preinscription->paiement->date_paiement->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Agent:</td>
                                    <td>{{ $preinscription->paiement->agent->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($preinscription->paiement->commentaire)
                    <div class="alert alert-secondary mt-3">
                        <strong>Commentaire paiement:</strong>
                        <p class="mb-0 mt-1">{{ $preinscription->paiement->commentaire }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Historique des modifications -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historique
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Création du dossier</h6>
                                <p class="text-muted mb-0">{{ $preinscription->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($preinscription->date_traitement)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Dossier traité</h6>
                                <p class="text-muted mb-0">{{ $preinscription->date_traitement->format('d/m/Y à H:i') }} par {{ $preinscription->agent->name ?? 'Système' }}</p>
                                @if($preinscription->commentaire_agent)
                                <p class="mb-0 mt-1"><strong>Commentaire:</strong> {{ $preinscription->commentaire_agent }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Dernière modification</h6>
                                <p class="text-muted mb-0">{{ $preinscription->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les actions -->
<!-- Modal pour validation -->
<div class="modal fade" id="validerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check me-2"></i>Valider le Dossier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.preinscriptions.valider', $preinscription) }}">
                @csrf
                <div class="modal-body">
                    <p>Confirmez la validation du dossier <strong>{{ $preinscription->numero_dossier }}</strong> ?</p>
                    <div class="mb-3">
                        <label for="commentaire_validation" class="form-label">Commentaire (optionnel)</label>
                        <textarea class="form-control" id="commentaire_validation" name="commentaire" rows="3" placeholder="Ajouter un commentaire..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer la validation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour rejet -->
<div class="modal fade" id="rejeterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times me-2"></i>Rejeter le Dossier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.preinscriptions.rejeter', $preinscription) }}">
                @csrf
                <div class="modal-body">
                    <p>Confirmez le rejet du dossier <strong>{{ $preinscription->numero_dossier }}</strong> ?</p>
                    <div class="mb-3">
                        <label for="commentaire_rejet" class="form-label">Motif du rejet *</label>
                        <textarea class="form-control" id="commentaire_rejet" name="commentaire" rows="3" required placeholder="Expliquez le motif du rejet..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour reclassement -->
<div class="modal fade" id="reclasserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Reclasser le Dossier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.preinscriptions.reclasser', $preinscription) }}">
                @csrf
                <div class="modal-body">
                    <p>Confirmez le reclassement du dossier <strong>{{ $preinscription->numero_dossier }}</strong> ?</p>
                    <div class="mb-3">
                        <label for="commentaire_reclassement" class="form-label">Motif du reclassement *</label>
                        <textarea class="form-control" id="commentaire_reclassement" name="commentaire" rows="3" required placeholder="Expliquez le motif du reclassement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Confirmer le reclassement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour mise en attente -->
<div class="modal fade" id="mettreAttenteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clock me-2"></i>Remettre en Attente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.preinscriptions.mettre-en-attente', $preinscription) }}">
                @csrf
                <div class="modal-body">
                    <p>Confirmez la remise en attente du dossier <strong>{{ $preinscription->numero_dossier }}</strong> ?</p>
                    <div class="mb-3">
                        <label for="commentaire_attente" class="form-label">Commentaire *</label>
                        <textarea class="form-control" id="commentaire_attente" name="commentaire" rows="3" required placeholder="Expliquez pourquoi ce dossier est remis en attente..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour rejet de paiement -->
<div class="modal fade" id="rejeterPaiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times me-2"></i>Rejeter le Paiement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.paiements.rejeter', $preinscription->paiement) }}">
                @csrf
                <div class="modal-body">
                    <p>Confirmez le rejet du paiement pour le dossier <strong>{{ $preinscription->numero_dossier }}</strong> ?</p>
                    <div class="mb-3">
                        <label for="commentaire_rejet_paiement" class="form-label">Motif du rejet *</label>
                        <textarea class="form-control" id="commentaire_rejet_paiement" name="commentaire" rows="3" required placeholder="Expliquez le motif du rejet du paiement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    padding-bottom: 10px;
}

.table-borderless td {
    border: none;
    padding: 0.3rem 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des modals
    const validerModal = document.getElementById('validerModal');
    const rejeterModal = document.getElementById('rejeterModal');
    
    if(validerModal) {
        validerModal.addEventListener('show.bs.modal', function() {
            console.log('Modal validation ouvert');
        });
    }
    
    if(rejeterModal) {
        rejeterModal.addEventListener('show.bs.modal', function() {
            console.log('Modal rejet ouvert');
        });
    }
});
</script>
@endpush