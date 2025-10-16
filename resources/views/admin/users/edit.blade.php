@extends('layouts.app')

@section('title', 'Modifier l\'Utilisateur')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Modifier l'Utilisateur: {{ $user->name }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-{{ $user->role === 'admin' ? 'primary' : 'info' }} text-white rounded-circle me-3 d-flex align-items-center justify-content-center"
                                         style="width: 60px; height: 60px; font-size: 18px;">
                                        {{ $user->initiales }}
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        <p class="text-muted mb-0">
                                            Membre depuis {{ $user->created_at->format('d/m/Y') }}
                                            • 
                                            <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                                {{ $user->statut_label }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Nom complet *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">Adresse email *</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label fw-bold">Rôle *</label>
                                    <select class="form-select @error('role') is-invalid @enderror" 
                                            id="role" 
                                            name="role" 
                                            required>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                        <option value="agent" {{ old('role', $user->role) == 'agent' ? 'selected' : '' }}>Agent</option>
                                    </select>
                                    @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Statut</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Utilisateur actif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques de l'utilisateur -->
                        @if($user->isAgent())
                        <div class="alert alert-info">
                            <h6><i class="fas fa-chart-bar me-2"></i>Performance de l'agent</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h5 mb-1 text-primary">{{ $user->preinscriptions->count() }}</div>
                                        <small class="text-muted">Dossiers assignés</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h5 mb-1 text-success">{{ $user->preinscriptionsTraitees()->count() }}</div>
                                        <small class="text-muted">Dossiers traités</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h5 mb-1 text-warning">{{ $user->taux_traitement }}%</div>
                                        <small class="text-muted">Taux de traitement</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline ms-2">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-{{ $user->is_active ? 'warning' : 'success' }}"
                                            onclick="return confirm('Êtes-vous sûr de vouloir {{ $user->is_active ? 'désactiver' : 'activer' }} cet utilisateur ?')">
                                        <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} me-1"></i>
                                        {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                @endif
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Section dangereuse -->
                    @if($user->id !== auth()->id())
                    <hr class="my-4">
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Zone dangereuse</h6>
                        <p class="mb-2">Cette action est irréversible. Une fois supprimé, l'utilisateur ne pourra plus accéder au système.</p>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('⚠️ ATTENTION! Cette action est définitive. Supprimer l\\'utilisateur {{ $user->name }} ?')">
                                <i class="fas fa-trash me-1"></i>Supprimer définitivement
                            </button>
                        </form>
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
}
</style>
@endpush