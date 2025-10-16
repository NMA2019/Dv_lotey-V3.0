@extends('layouts.app')

@section('title', 'Statistiques des Utilisateurs')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques des Utilisateurs
                    </h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="periode" class="form-label fw-bold">Période</label>
                            <select class="form-select" id="periode">
                                <option value="week">Cette semaine</option>
                                <option value="month" selected>Ce mois</option>
                                <option value="quarter">Ce trimestre</option>
                                <option value="year">Cette année</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_debut" class="form-label fw-bold">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_fin" class="form-label fw-bold">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Cartes de statistiques -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Utilisateurs
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Utilisateurs Actifs
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeUsers }}</div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                {{ round(($activeUsers / $totalUsers) * 100, 1) }}% du total
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Administrateurs
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $adminCount }}</div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                {{ round(($adminCount / $totalUsers) * 100, 1) }}% du total
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Taux d'Activité Moyen
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $averageActivity }}%</div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                Performance moyenne des agents
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Répartition par Rôle</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="roleChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Performance des Agents</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="performanceChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des performances -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Détail des Performances par Agent</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Agent</th>
                                                    <th>Dossiers Assignés</th>
                                                    <th>Validés</th>
                                                    <th>Rejetés</th>
                                                    <th>En Attente</th>
                                                    <th>Taux de Traitement</th>
                                                    <th>Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($agentStats as $agent)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                                 style="width: 35px; height: 35px; font-size: 12px;">
                                                                {{ $agent->initiales }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">{{ $agent->name }}</div>
                                                                <small class="text-muted">{{ $agent->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $agent->total_dossiers }}</td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $agent->valides }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger">{{ $agent->rejetes }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning">{{ $agent->en_attente }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 8px; width: 100px;">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: {{ $agent->taux_traitement }}%"
                                                                 title="{{ $agent->taux_traitement }}%">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">{{ $agent->taux_traitement }}%</small>
                                                    </td>
                                                    <td>
                                                        @if($agent->taux_traitement >= 80)
                                                            <span class="badge bg-success">Excellent</span>
                                                        @elseif($agent->taux_traitement >= 60)
                                                            <span class="badge bg-info">Bon</span>
                                                        @elseif($agent->taux_traitement >= 40)
                                                            <span class="badge bg-warning">Moyen</span>
                                                        @else
                                                            <span class="badge bg-danger">Faible</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                                        <p>Aucune donnée de performance disponible</p>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques d'activité -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Activité Récente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        @forelse($recentActivity as $activity)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $activity->user->name }}</div>
                                                <small class="text-muted">{{ $activity->description }}</small>
                                            </div>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                        @empty
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                            <p>Aucune activité récente</p>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Évolution des Utilisateurs</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="evolutionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des rôles
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    const roleChart = new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: ['Administrateurs', 'Agents'],
            datasets: [{
                data: [{{ $adminCount }}, {{ $agentCount }}],
                backgroundColor: ['#4e73df', '#1cc88a'],
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

    // Graphique de performance
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($agentNames) !!},
            datasets: [{
                label: 'Taux de traitement (%)',
                data: {!! json_encode($agentPerformance) !!},
                backgroundColor: '#4e73df',
                borderColor: '#2e59d9',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Graphique d'évolution
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Nouveaux Utilisateurs',
                data: [2, 3, 1, 4, 2, 3],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
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

    // Gestion des filtres
    document.getElementById('periode').addEventListener('change', function() {
        chargerStats();
    });

    function chargerStats() {
        const periode = document.getElementById('periode').value;
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;
        
        // Implémenter le rechargement des stats via AJAX
        console.log('Chargement stats:', { periode, dateDebut, dateFin });
    }
});
</script>
@endpush

@push('styles')
<style>
.avatar {
    font-weight: bold;
    font-size: 12px;
}
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
</style>
@endpush