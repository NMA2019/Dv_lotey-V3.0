<!-- resources/views/admin/preinscriptions/index.blade.php -->
@extends('layouts.app')

@section('title', 'Gestion des Préinscriptions')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Liste des Préinscriptions
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <select class="form-select" onchange="window.location.href=this.value">
                                <option value="{{ route('admin.preinscriptions.index') }}">Tous les statuts</option>
                                <option value="{{ route('admin.preinscriptions.index') }}?statut=en_attente" 
                                        {{ request('statut') == 'en_attente' ? 'selected' : '' }}>
                                    En attente
                                </option>
                                <option value="{{ route('admin.preinscriptions.index') }}?statut=valide"
                                        {{ request('statut') == 'valide' ? 'selected' : '' }}>
                                    Validées
                                </option>
                                <option value="{{ route('admin.preinscriptions.index') }}?statut=rejete"
                                        {{ request('statut') == 'rejete' ? 'selected' : '' }}>
                                    Rejetées
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <form method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Rechercher..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="{{ route('admin.pdf.preinscriptions.export') }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Exporter
                            </a>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Dossier</th>
                                    <th>Nom Complet</th>
                                    <th>Contact</th>
                                    <th>Date Inscription</th>
                                    <th>Rendez-vous</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($preinscriptions as $preinscription)
                                <tr>
                                    <td>
                                        <strong>{{ $preinscription->numero_dossier }}</strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $preinscription->nom }} {{ $preinscription->prenom }}</div>
                                        <small class="text-muted">Né le {{ $preinscription->date_naissance->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $preinscription->email }}</div>
                                        <small class="text-muted">{{ $preinscription->telephone }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $preinscription->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $preinscription->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $preinscription->date_rendez_vous->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $preinscription->heure_rendez_vous }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $preinscription->statut === 'valide' ? 'success' : ($preinscription->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                            {{ $preinscription->statut === 'valide' ? 'Validé' : ($preinscription->statut === 'en_attente' ? 'En attente' : 'Rejeté') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.preinscriptions.show', $preinscription) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($preinscription->statut === 'en_attente')
                                            <a href="{{ route('admin.preinscriptions.show', $preinscription) }}#traiter" 
                                               class="btn btn-outline-warning"
                                               title="Traiter">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>Aucune préinscription trouvée</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $preinscriptions->firstItem() }} à {{ $preinscriptions->lastItem() }} sur {{ $preinscriptions->total() }} résultats
                        </div>
                        {{ $preinscriptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection