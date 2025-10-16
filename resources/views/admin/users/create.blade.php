@extends('layouts.app')

@section('title', 'Créer un Utilisateur')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Créer un Nouvel Utilisateur
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

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Nom complet *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required 
                                           placeholder="Ex: Jean Dupont">
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
                                           value="{{ old('email') }}" 
                                           required 
                                           placeholder="Ex: jean.dupont@example.com">
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
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                        <option value="agent" {{ old('role') == 'agent' ? 'selected' : '' }}>Agent</option>
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
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="is_active">
                                            Utilisateur actif
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Les utilisateurs inactifs ne peuvent pas se connecter
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">Mot de passe *</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           placeholder="Minimum 8 caractères">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-bold">Confirmation du mot de passe *</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required 
                                           placeholder="Répétez le mot de passe">
                                </div>
                            </div>
                        </div>

                        <!-- Informations sur les rôles -->
                        <div class="alert alert-info" id="roleInfo">
                            <h6><i class="fas fa-shield-alt me-2"></i>Permissions par rôle</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Administrateur:</strong>
                                    <ul class="small mb-0">
                                        <li>Gestion complète des utilisateurs</li>
                                        <li>Accès à toutes les préinscriptions</li>
                                        <li>Configuration du système</li>
                                        <li>Génération de rapports</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <strong>Agent:</strong>
                                    <ul class="small mb-0">
                                        <li>Gestion des préinscriptions assignées</li>
                                        <li>Validation des dossiers</li>
                                        <li>Consultation limitée</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const roleInfo = document.getElementById('roleInfo');

    function toggleRoleInfo() {
        if (roleSelect.value === 'admin') {
            roleInfo.classList.remove('alert-warning');
            roleInfo.classList.add('alert-info');
        } else if (roleSelect.value === 'agent') {
            roleInfo.classList.remove('alert-info');
            roleInfo.classList.add('alert-warning');
        }
    }

    roleSelect.addEventListener('change', toggleRoleInfo);
    toggleRoleInfo(); // Initial call
});
</script>
@endpush