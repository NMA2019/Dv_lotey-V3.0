@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Gestion des Utilisateurs
                    </h5>
                    <div>
                        <a href="{{ route('admin.users.stats') }}" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-chart-bar me-1"></i>Statistiques
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>Nouvel Utilisateur
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Messages de statut -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <select class="form-select" onchange="window.location.href=this.value">
                                <option value="{{ route('admin.users.index') }}">Tous les rôles</option>
                                <option value="{{ route('admin.users.index') }}?role=admin" 
                                        {{ request('role') == 'admin' ? 'selected' : '' }}>
                                    Administrateurs
                                </option>
                                <option value="{{ route('admin.users.index') }}?role=agent"
                                        {{ request('role') == 'agent' ? 'selected' : '' }}>
                                    Agents
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" onchange="window.location.href=this.value">
                                <option value="{{ route('admin.users.index') }}">Tous les statuts</option>
                                <option value="{{ route('admin.users.index') }}?statut=actif" 
                                        {{ request('statut') == 'actif' ? 'selected' : '' }}>
                                    Actifs
                                </option>
                                <option value="{{ route('admin.users.index') }}?statut=inactif"
                                        {{ request('statut') == 'inactif' ? 'selected' : '' }}>
                                    Inactifs
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <form method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Rechercher un utilisateur..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Stats rapides -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $users->total() }}</h4>
                                    <small>Total Utilisateurs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $users->where('is_active', true)->count() }}</h4>
                                    <small>Utilisateurs Actifs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $users->where('role', 'admin')->count() }}</h4>
                                    <small>Administrateurs</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center py-3">
                                    <h4>{{ $users->where('role', 'agent')->count() }}</h4>
                                    <small>Agents</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Contact</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Performance</th>
                                    <th>Dernière activité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-{{ $user->role === 'admin' ? 'primary' : 'info' }} text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                {{ $user->initiales }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <small class="text-muted">ID: {{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $user->email }}</div>
                                        <small class="text-muted">Inscrit le {{ $user->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'info' }}">
                                            <i class="fas fa-{{ $user->role === 'admin' ? 'crown' : 'user' }} me-1"></i>
                                            {{ $user->role_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                            <i class="fas fa-{{ $user->is_active ? 'check' : 'times' }} me-1"></i>
                                            {{ $user->statut_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->isAgent())
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $user->taux_traitement }}%"
                                                     title="Taux de traitement: {{ $user->taux_traitement }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $user->taux_traitement }}% traité</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $user->updated_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip"
                                               title="Modifier l'utilisateur">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir {{ $user->is_active ? 'désactiver' : 'activer' }} cet utilisateur ?')">
                                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-outline-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="Supprimer l'utilisateur"
                                                        onclick="return confirm('⚠️ Attention! Cette action est irréversible. Supprimer cet utilisateur ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @else
                                            <span class="btn btn-outline-secondary disabled" title="Vous ne pouvez pas modifier votre propre statut">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <p>Aucun utilisateur trouvé</p>
                                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>Créer le premier utilisateur
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} résultats
                        </div>
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar {
    font-weight: bold;
    font-size: 14px;
}
.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}
.table th {
    border-top: none;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
});
</script>
@endpush