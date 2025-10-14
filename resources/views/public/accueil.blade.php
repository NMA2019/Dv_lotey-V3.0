@extends('layouts.app')

@section('title', 'Accueil')
@section('content')
<div class="hero-section" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); min-height: 80vh; display: flex; align-items: center;">
    <div class="container text-white">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Préinscription Loterie Américaine</h1>
                <p class="lead mb-4">Simplifiez votre processus de participation à la loterie américaine avec notre plateforme de préinscription en ligne.</p>
                <a href="{{ route('preinscription.etape1') }}" class="btn btn-light btn-lg px-5 py-3">Commencer la Préinscription</a>
            </div>
            <div class="col-lg-6">
                <!-- Image illustrative -->
                <div class="text-center">
                    <img src="{{ asset('images/lottery-hero.png') }}" alt="Loterie Américaine" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h5>Processus Rapide</h5>
                        <p>Préinscription en quelques étapes simples</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5>Sécurisé</h5>
                        <p>Vos données sont protégées et sécurisées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h5>Support</h5>
                        <p>Assistance disponible pour vous accompagner</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection