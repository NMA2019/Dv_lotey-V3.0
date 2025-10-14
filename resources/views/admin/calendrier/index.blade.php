<!-- resources/views/admin/calendrier/index.blade.php -->
@extends('layouts.app')

@section('title', 'Gestion du Calendrier')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Gestion du Calendrier
                    </h5>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#genererModal">
                        <i class="fas fa-plus me-1"></i>Générer Créneaux
                    </button>
                </div>
                <div class="card-body">
                    <!-- Tableau des créneaux -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Créneau</th>
                                    <th>Capacité</th>
                                    <th>Réservations</th>
                                    <th>Places Restantes</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($creneaux as $creneau)
                                <tr>
                                    <td>
                                        <strong>{{ $creneau->date_creneau->format('d/m/Y') }}</strong>
                                        <div class="text-muted small">{{ $creneau->date_creneau->locale('fr')->dayName }}</div>
                                    </td>
                                    <td>
                                        {{ $creneau->heure_debut }} - {{ $creneau->heure_fin }}
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" 
                                               value="{{ $creneau->capacite_max }}" 
                                               onchange="updateCreneau({{ $creneau->id }}, 'capacite_max', this.value)"
                                               style="width: 80px;">
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $creneau->reservations }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $creneau->places_restantes > 0 ? 'success' : 'danger' }}">
                                            {{ $creneau->places_restantes }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   {{ $creneau->est_actif ? 'checked' : '' }}
                                                   onchange="updateCreneau({{ $creneau->id }}, 'est_actif', this.checked ? 1 : 0)">
                                            <label class="form-check-label">
                                                {{ $creneau->est_actif ? 'Actif' : 'Inactif' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="desactiverCreneau({{ $creneau->id }})"
                                                title="Désactiver">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                        <p>Aucun créneau planifié</p>
                                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#genererModal">
                                            Générer des créneaux
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $creneaux->firstItem() }} à {{ $creneaux->lastItem() }} sur {{ $creneaux->total() }} résultats
                        </div>
                        {{ $creneaux->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Génération Créneaux -->
<div class="modal fade" id="genererModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Générer des Créneaux</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.calendrier.creneaux.generer') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" 
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" 
                               value="{{ now()->addDays(30)->format('Y-m-d') }}" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Les créneaux seront générés du lundi au vendredi, hors weekends.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Générer les créneaux</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateCreneau(creneauId, champ, valeur) {
    fetch(`/admin/calendrier/creneaux/${creneauId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            [champ]: valeur
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Créneau mis à jour avec succès', 'success');
        } else {
            showToast('Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erreur de connexion', 'error');
    });
}

function desactiverCreneau(creneauId) {
    if (confirm('Êtes-vous sûr de vouloir désactiver ce créneau ?')) {
        updateCreneau(creneauId, 'est_actif', 0);
    }
}

function showToast(message, type = 'info') {
    // Implémentation simple d'un toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush