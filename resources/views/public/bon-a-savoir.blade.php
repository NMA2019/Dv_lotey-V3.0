<!-- resources/views/public/bon-a-savoir.blade.php -->
@extends('layouts.app')

@section('title', 'Bon à Savoir - Documents Requis')
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Bon à Savoir</h1>
                <p class="lead">Toutes les informations importantes pour préparer votre dossier de préinscription</p>
            </div>

            <!-- Documents requis -->
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Documents à Préparer</h4>
                </div>
                <div class="card-body">
                    @foreach($documents as $document)
                    <div class="document-item mb-4 p-3 border rounded">
                        <h5 class="text-primary">{{ $document['titre'] }}</h5>
                        <p class="mb-2">{{ $document['description'] }}</p>
                        <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>{{ $document['details'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Qualité photo -->
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-camera me-2"></i>Qualité des Photos d'Identité</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">Exigences techniques</h6>
                            <ul class="list-unstyled">
                                @foreach(array_slice($qualitesPhoto, 0, 4) as $qualite)
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>{{ $qualite }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">À éviter</h6>
                            <ul class="list-unstyled">
                                @foreach(array_slice($qualitesPhoto, 4) as $qualite)
                                <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>{{ $qualite }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conseils supplémentaires -->
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Conseils Importants</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-clock me-2"></i>Délais de traitement</h6>
                                <p class="mb-0">Prévoyez 48 à 72 heures pour le traitement complet de votre dossier après le rendez-vous.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-euro-sign me-2"></i>Frais supplémentaires</h6>
                                <p class="mb-0">Aucun frais supplémentaire n'est requis après la préinscription.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection