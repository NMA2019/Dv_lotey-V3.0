<!-- resources/views/admin/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Tableau de Bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.preinscriptions.index') }}">
                            <i class="fas fa-list me-2"></i>
                            Préinscriptions
                            <span class="badge bg-primary float-end">{{ $stats['total_preinscriptions'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.preinscriptions.index', ['statut' => 'en_attente']) }}">
                            <i class="fas fa-clock me-2"></i>
                            En Attente
                            <span class="badge bg-warning float-end">{{ $stats['en_attente'] }}</span>
                        </a>
                    </li>
                    @if(Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-2"></i>
                            Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-chart-bar me-2"></i>
                            Rapports
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog me-2"></i>
                            Paramètres
                        </a>
                    </li>
                </ul>

                <!-- Stats rapides -->
                <div class="mt-4 p-3 bg-white rounded shadow-sm">
                    <h6 class="border-bottom pb-2">Aujourd'hui</h6>
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>Nouvelles:</span>
                            <strong class="text-primary">{{ $todayStats['nouvelles'] ?? 0 }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Traitées:</span>
                            <strong class="text-success">{{ $todayStats['traitees'] ?? 0 }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Rendez-vous:</span>
                            <strong class="text-warning">{{ $todayStats['rendez_vous'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- En-tête -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de Bord
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#quickStatsModal">
                        <i class="fas fa-chart-line me-1"></i>Vue détaillée
                    </button>
                </div>
            </div>

            <!-- Cartes de statistiques -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-primary h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Préinscriptions
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_preinscriptions'] }}</div>
                                    <div class="mt-2 mb-0 text-muted text-xs">
                                        <span class="text-success me-2">
                                            <i class="fas fa-arrow-up"></i> 12%
                                        </span>
                                        <span>Depuis le mois dernier</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-success h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Validées
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['valides'] }}</div>
                                    <div class="mt-2 mb-0 text-muted text-xs">
                                        {{ $stats['total_preinscriptions'] > 0 ? round(($stats['valides'] / $stats['total_preinscriptions']) * 100, 1) : 0 }}% du total
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-warning h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        En Attente
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['en_attente'] }}</div>
                                    <div class="mt-2 mb-0 text-muted text-xs">
                                        <span class="text-warning">
                                            <i class="fas fa-clock"></i> Nécessitent attention
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card border-left-danger h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Rejetées
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejetees'] }}</div>
                                    <div class="mt-2 mb-0 text-muted text-xs">
                                        {{ $stats['total_preinscriptions'] > 0 ? round(($stats['rejetees'] / $stats['total_preinscriptions']) * 100, 1) : 0 }}% du total
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques et tableaux -->
            <div class="row">
                <!-- Préinscriptions récentes -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Préinscriptions Récentes</h6>
                            <a href="{{ route('admin.preinscriptions.index') }}" class="btn btn-sm btn-outline-primary">
                                Voir tout <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>N° Dossier</th>
                                            <th>Nom Complet</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentPreinscriptions as $preinscription)
                                        <tr>
                                            <td>
                                                <strong>{{ $preinscription->numero_dossier }}</strong>
                                            </td>
                                            <td>
                                                <div>{{ $preinscription->nom_complet }}</div>
                                                <small class="text-muted">{{ $preinscription->email }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $preinscription->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $preinscription->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $preinscription->statut_css_class }}">
                                                    {{ $preinscription->statut_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.preinscriptions.show', $preinscription) }}" 
                                                       class="btn btn-outline-primary" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($preinscription->peutEtreModifie())
                                                    <button class="btn btn-outline-warning"
                                                            data-bs-toggle="tooltip"
                                                            title="Traiter le dossier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>Aucune préinscription récente</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rendez-vous et stats -->
                <div class="col-lg-4 mb-4">
                    <!-- Rendez-vous du jour -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Rendez-vous Aujourd'hui</h6>
                        </div>
                        <div class="card-body">
                            @forelse($rdvAujourdhui as $rdv)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded p-2 text-center">
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($rdv->heure_rendez_vous)->format('H') }}</div>
                                        <small>h</small>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">{{ $rdv->nom_complet }}</div>
                                    <small class="text-muted">{{ $rdv->numero_dossier }}</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-{{ $rdv->statut_css_class }}">{{ $rdv->statut_label }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                <p>Aucun rendez-vous aujourd'hui</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Stats rapides -->
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Statistiques Rapides</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-1 text-success">{{ $stats['valides'] }}</div>
                                        <small class="text-muted">Validés</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-1 text-warning">{{ $stats['en_attente'] }}</div>
                                        <small class="text-muted">En attente</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-1 text-danger">{{ $stats['rejetees'] }}</div>
                                        <small class="text-muted">Rejetés</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal pour les stats détaillées -->
<div class="modal fade" id="quickStatsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Statistiques Détaillées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Contenu des stats détaillées -->
                <p>Fonctionnalité à implémenter avec des graphiques Chart.js</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualisation des données
    document.getElementById('refreshBtn').addEventListener('click', function() {
        const button = this;
        const originalHtml = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualisation...';
        button.disabled = true;
        
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
    
    // Initialiser les tooltips Bootstrap
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
});
</script>
@endpush