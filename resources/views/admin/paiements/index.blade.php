<!-- resources/views/admin/paiements/index.blade.php -->
@extends('layouts.app')

@section('title', 'Gestion des Paiements')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill me-2"></i>
                        Gestion des Paiements
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <select class="form-select" onchange="window.location.href=this.value + '?statut=' + this.options[this.selectedIndex].value">
                                <option value="{{ route('admin.paiements.index') }}">Tous les statuts</option>
                                <option value="{{ route('admin.paiements.index') }}" 
                                        {{ request('statut') == 'en_attente' ? 'selected' : '' }}>
                                    En attente
                                </option>
                                <option value="{{ route('admin.paiements.index') }}"
                                        {{ request('statut') == 'valide' ? 'selected' : '' }}>
                                    Validés
                                </option>
                                <option value="{{ route('admin.paiements.index') }}"
                                        {{ request('statut') == 'rejete' ? 'selected' : '' }}>
                                    Rejetés
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <form method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Rechercher par référence, nom, dossier..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="{{ route('admin.pdf.paiements.report') }}" class="btn btn-success">
                                <i class="fas fa-file-pdf me-2"></i>Rapport PDF
                            </a>
                        </div>
                    </div>

                    <!-- Stats rapides -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $stats['total'] }}</h4>
                                    <small>Total Paiements</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $stats['en_attente'] }}</h4>
                                    <small>En Attente</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $stats['valides'] }}</h4>
                                    <small>Validés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $stats['rejetes'] }}</h4>
                                    <small>Rejetés</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Dossier</th>
                                    <th>Client</th>
                                    <th>Mode</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paiements as $paiement)
                                <tr>
                                    <td>
                                        <strong>{{ $paiement->reference_paiement ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ $paiement->preinscription->numero_dossier }}</strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $paiement->preinscription->nom }} {{ $paiement->preinscription->prenom }}</div>
                                        <small class="text-muted">{{ $paiement->preinscription->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $paiement->mode_paiement }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</strong>
                                    </td>
                                    <td>
                                        <div>{{ $paiement->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $paiement->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $paiement->statut === 'valide' ? 'success' : ($paiement->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                            {{ $paiement->statut === 'valide' ? 'Validé' : ($paiement->statut === 'en_attente' ? 'En attente' : 'Rejeté') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($paiement->statut === 'en_attente')
                                            <form method="POST" action="{{ route('admin.paiements.valider', $paiement) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Valider le paiement">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.paiements.rejeter', $paiement) }}" class="d-inline">
                                                @csrf
                                                <button type="button" class="btn btn-outline-danger" 
                                                        title="Rejeter le paiement"
                                                        onclick="showRejetModal({{ $paiement->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <a href="{{ route('admin.preinscriptions.show', $paiement->preinscription_id) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Voir le dossier">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                        <p>Aucun paiement trouvé</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $paiements->firstItem() }} à {{ $paiements->lastItem() }} sur {{ $paiements->total() }} résultats
                        </div>
                        {{ $paiements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour rejet de paiement -->
<div class="modal fade" id="rejetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeter le Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejetForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Motif du rejet *</label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" required 
                                  placeholder="Expliquez pourquoi ce paiement est rejeté..."></textarea>
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

@push('scripts')
<script>
function showRejetModal(paiementId) {
    const form = document.getElementById('rejetForm');
    form.action = `/admin/paiements/${paiementId}/rejeter`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejetModal'));
    modal.show();
}
</script>
@endpush