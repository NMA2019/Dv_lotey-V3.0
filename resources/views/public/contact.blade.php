<!-- resources/views/public/contact.blade.php -->
@extends('layouts.app')

@section('title', 'Contact - Nous Contacter')
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Contactez-Nous</h1>
                <p class="lead">Nous sommes à votre écoute pour toute question</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations de contact -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h4 class="text-primary mb-4">Nos Coordonnées</h4>
                    
                    <div class="contact-info mb-4">
                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Adresse</h6>
                                <p class="text-muted mb-0">{{ $coordonnees['adresse'] }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-phone text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Téléphone</h6>
                                <p class="text-muted mb-0">{{ $coordonnees['telephone'] }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="fas fa-envelope text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted mb-0">{{ $coordonnees['email'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Horaires d'ouverture -->
                    <div class="opening-hours">
                        <h6 class="fw-bold text-primary mb-3">Horaires d'Ouverture</h6>
                        @foreach($horaires as $horaire)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-medium">{{ $horaire['jour'] }}</span>
                            <span class="text-muted">{{ $horaire['heures'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de contact -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-primary mb-4">Envoyez-nous un message</h4>
                    
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label fw-bold">Nom complet *</label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sujet" class="form-label fw-bold">Sujet *</label>
                            <input type="text" class="form-control @error('sujet') is-invalid @enderror" 
                                   id="sujet" name="sujet" value="{{ old('sujet') }}" required>
                            @error('sujet')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label fw-bold">Message *</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="6" required>{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte Google Maps -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1989.8447270612178!2d9.769370206337941!3d4.0835079774003695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10610da68f8640f3%3A0x8618dc8446ea0114!2sCentre%20de%20Formation%20Professionnelle%20du%20Commerce%20et%20du%20Monde%20Digital!5e0!3m2!1sfr!2scm!4v1755138846248!5m2!1sfr!2scm" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection