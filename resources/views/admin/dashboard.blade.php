@extends('layouts.app')

@section('title', 'Tableau de Bord Admin')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2" style="color: #fff; text-align:center"></i>
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
                        <a class="nav-link" href="{{ route('admin.preinscriptions.index') }}?statut=en_attente">
                            <i class="fas fa-clock me-2"></i>
                            En Attente
                            <span class="badge bg-warning float-end">{{ $stats['en_attente'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.paiements.index') }}">
                            <i class="fas fa-money-bill me-2"></i>
                            Paiements
                            <span class="badge bg-info float-end">{{ $stats['paiements_attente'] }}</span>
                        </a>
                    </li>
                    @if(Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-2"></i>
                            Users
                            <span class="badge bg-secondary float-end">{{ $stats['total_agents'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="rapportsBtn">
                            <i class="fas fa-chart-bar me-2"></i>
                            Rapports
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.calendrier.index') }}">
                            <i class="fas fa-calendar me-2"></i>
                            Calendrier
                        </a>
                    </li>
                </ul>

                <!-- Stats rapides sidebar -->
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
                        <div class="d-flex justify-content-between">
                            <span>Paiements:</span>
                            <strong class="text-info">{{ $todayStats['paiements'] ?? 0 }}</strong>
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
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#statsModal">
                        <i class="fas fa-chart-line me-1"></i>Vue détaillée
                    </button>
                </div>
            </div>

            <!-- Cartes de statistiques -->
            <div class="row">
                <!-- Total Préinscriptions -->
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
                                            <i class="fas fa-arrow-up"></i> 
                                            {{ $stats['total_preinscriptions'] > 0 ? round(($stats['valides'] / $stats['total_preinscriptions']) * 100, 1) : 0 }}%
                                        </span>
                                        <span>Taux de validation</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Validées -->
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

                <!-- En Attente -->
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

                <!-- Rejetées -->
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
                                                <div>{{ $preinscription->nom }} {{ $preinscription->prenom }}</div>
                                                <small class="text-muted">{{ $preinscription->email }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $preinscription->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $preinscription->created_at->format('H:i') }}</small>
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
                                                       data-bs-toggle="tooltip" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($preinscription->statut === 'en_attente')
                                                    <button class="btn btn-outline-warning"
                                                            data-bs-toggle="tooltip"
                                                            title="Traiter le dossier"
                                                            onclick="traiterDossier({{ $preinscription->id }})">
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
                                        <small class="text-muted">Validées</small>
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
                                        <small class="text-muted">Rejetées</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-1 text-info">{{ $stats['paiements_valides'] }}</div>
                                        <small class="text-muted">Paiements</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="h6 mb-1 text-primary">{{ number_format($stats['revenus_mois'] / 1000, 0) }}K</div>
                                        <small class="text-muted">Revenus</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mt-4">
                <!-- Graphique des préinscriptions par statut -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Préinscriptions par Statut</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="statutChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Graphique des préinscriptions par mois -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Évolution Mensuelle</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal pour les stats détaillées -->
<div class="modal fade" id="statsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Statistiques Détaillées</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Période</h6>
                        <select class="form-select" id="periodeSelect">
                            <option value="week">Cette semaine</option>
                            <option value="month" selected>Ce mois</option>
                            <option value="year">Cette année</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <h6>&nbsp;</h6>
                        <button class="btn btn-primary w-100" onclick="chargerStatsDetaillees()">
                            <i class="fas fa-chart-bar me-2"></i>Générer le rapport
                        </button>
                    </div>
                </div>
                <div id="statsDetaillees" class="mt-4">
                    <!-- Les stats détaillées seront chargées ici -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stats-card {
    transition: all 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }

.sidebar {
    min-height: calc(100vh - 76px);
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}
.sidebar .nav-link {
    color: #333;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    margin-bottom: 0.25rem;
}
.sidebar .nav-link.active {
    color: #fff;
    background-color: #1e3c72;
}
.sidebar .nav-link:hover:not(.active) {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les graphiques
    initialiserGraphiques();
    
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

function initialiserGraphiques() {
    // Graphique des statuts
    const statutCtx = document.getElementById('statutChart').getContext('2d');
    const statutChart = new Chart(statutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Validées', 'En Attente', 'Rejetées', 'Reclassées'],
            datasets: [{
                data: [
                    {{ $stats['valides'] }},
                    {{ $stats['en_attente'] }},
                    {{ $stats['rejetees'] }},
                    {{ $stats['reclasses'] }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Graphique mensuel
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyData['labels'] ?? []) !!},
            datasets: [{
                label: 'Total Préinscriptions',
                data: {!! json_encode($monthlyData['total'] ?? []) !!},
                borderColor: '#1e3c72',
                backgroundColor: 'rgba(30, 60, 114, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Validées',
                data: {!! json_encode($monthlyData['valides'] ?? []) !!},
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function traiterDossier(id) {
    window.location.href = `/admin/preinscriptions/${id}`;
}

function chargerStatsDetaillees() {
    const periode = document.getElementById('periodeSelect').value;
    const container = document.getElementById('statsDetaillees');
    
    container.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des statistiques...</p>
        </div>
    `;

    fetch(`/admin/stats?periode=${periode}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>${data.data.preinscriptions}</h3>
                                    <p>Préinscriptions</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>${data.data.paiements_valides}</h3>
                                    <p>Paiements Validés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>${data.data.revenus ? (data.data.revenus / 1000).toFixed(0) + 'K' : '0'}</h3>
                                    <p>Revenus (FCFA)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        Erreur lors du chargement des statistiques
                    </div>
                `;
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">
                    Erreur de connexion
                </div>
            `;
            console.error('Erreur:', error);
        });
}

// Gestion du menu Rapports
document.getElementById('rapportsBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    const modal = new bootstrap.Modal(document.getElementById('statsModal'));
    modal.show();
});
</script>
@endpush