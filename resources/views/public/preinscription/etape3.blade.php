<!-- resources/views/public/preinscription/etape3.blade.php -->
@extends('layouts.app')

@section('title', 'Préinscription - Rendez-vous')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Indicateur de progression -->
            <div class="step-progress mb-5">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <div class="step-label">Informations</div>
                </div>
                <div class="step completed">
                    <div class="step-number">2</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step active">
                    <div class="step-number">3</div>
                    <div class="step-label">Rendez-vous</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <div class="card shadow-lg border-0 fade-in">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Étape 3: Prise de Rendez-vous</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('preinscription.etape3.store') }}" method="POST">
                        @csrf
                        
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Important :</strong> Veuillez sélectionner une date et un créneau horaire pour votre rendez-vous en agence.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="date_rendez_vous" class="form-label fw-bold">Date du Rendez-vous *</label>
                                <input type="date" 
                                       class="form-control @error('date_rendez_vous') is-invalid @enderror" 
                                       id="date_rendez_vous" 
                                       name="date_rendez_vous" 
                                       value="{{ old('date_rendez_vous') }}" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       required>
                                @error('date_rendez_vous')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-calendar me-1"></i>
                                    Les rendez-vous sont disponibles du lundi au vendredi
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="heure_rendez_vous" class="form-label fw-bold">Heure du Rendez-vous *</label>
                                <select class="form-select @error('heure_rendez_vous') is-invalid @enderror" 
                                        id="heure_rendez_vous" 
                                        name="heure_rendez_vous" 
                                        required>
                                    <option value="">Sélectionnez un créneau</option>
                                    <option value="08:00" {{ old('heure_rendez_vous') == '08:00' ? 'selected' : '' }}>08:00 - 09:00</option>
                                    <option value="09:00" {{ old('heure_rendez_vous') == '09:00' ? 'selected' : '' }}>09:00 - 10:00</option>
                                    <option value="10:00" {{ old('heure_rendez_vous') == '10:00' ? 'selected' : '' }}>10:00 - 11:00</option>
                                    <option value="11:00" {{ old('heure_rendez_vous') == '11:00' ? 'selected' : '' }}>11:00 - 12:00</option>
                                    <option value="14:00" {{ old('heure_rendez_vous') == '14:00' ? 'selected' : '' }}>14:00 - 15:00</option>
                                    <option value="15:00" {{ old('heure_rendez_vous') == '15:00' ? 'selected' : '' }}>15:00 - 16:00</option>
                                    <option value="16:00" {{ old('heure_rendez_vous') == '16:00' ? 'selected' : '' }}>16:00 - 17:00</option>
                                </select>
                                @error('heure_rendez_vous')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Calendrier des disponibilités -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Disponibilités des prochains jours</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2" id="availability-calendar">
                                    <!-- Les disponibilités seront chargées en JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Informations du rendez-vous -->
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Informations importantes</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        <strong>Lieu :</strong> Centre de Formation Professionnelle du Commerce et du Monde Digital, Douala
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 17h00
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        <strong>À apporter :</strong> Pièce d'identité, justificatif de paiement, photos d'identité
                                    </li>
                                    <li>
                                        <i class="fas fa-user-clock text-primary me-2"></i>
                                        <strong>Durée estimée :</strong> 30-45 minutes
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('preinscription.etape2') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Vérifier le Récapitulatif <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date_rendez_vous');
    const timeSelect = document.getElementById('heure_rendez_vous');
    const calendar = document.getElementById('availability-calendar');

    // Générer le calendrier des disponibilités
    function generateAvailabilityCalendar() {
        const today = new Date();
        const dates = [];
        
        // Générer 14 jours à partir de demain
        for (let i = 1; i <= 14; i++) {
            const date = new Date(today);
            date.setDate(today.getDate() + i);
            
            // Exclure les weekends
            if (date.getDay() !== 0 && date.getDay() !== 6) {
                dates.push(new Date(date));
            }
        }

        calendar.innerHTML = dates.map(date => {
            const dateStr = date.toISOString().split('T')[0];
            const formattedDate = date.toLocaleDateString('fr-FR', {
                weekday: 'short',
                day: 'numeric',
                month: 'short'
            });
            
            // Simuler la disponibilité (en production, ça viendrait de l'API)
            const availableSlots = Math.floor(Math.random() * 4) + 2; // 2-5 créneaux disponibles
            
            return `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="availability-day card h-100 text-center ${availableSlots > 0 ? 'border-success' : 'border-secondary'}">
                        <div class="card-body p-2">
                            <div class="fw-bold">${formattedDate}</div>
                            <div class="small text-muted">${availableSlots} créneaux</div>
                            <button type="button" class="btn btn-sm ${availableSlots > 0 ? 'btn-outline-success' : 'btn-outline-secondary'} mt-1" 
                                    ${availableSlots === 0 ? 'disabled' : ''}
                                    onclick="selectDate('${dateStr}')">
                                Choisir
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Sélectionner une date depuis le calendrier
    window.selectDate = function(dateStr) {
        dateInput.value = dateStr;
        
        // Mettre à jour l'apparence des cartes
        document.querySelectorAll('.availability-day').forEach(card => {
            card.classList.remove('border-primary', 'bg-light');
        });
        
        event.target.closest('.availability-day').classList.add('border-primary', 'bg-light');
        
        // Simuler le chargement des créneaux disponibles
        loadAvailableTimeSlots(dateStr);
    }

    // Charger les créneaux horaires disponibles
    function loadAvailableTimeSlots(date) {
        // En production, ça ferait un appel API
        const allSlots = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
        const availableSlots = allSlots.filter(() => Math.random() > 0.3); // 70% de disponibilité
        
        timeSelect.innerHTML = '<option value="">Sélectionnez un créneau</option>' +
            availableSlots.map(slot => 
                `<option value="${slot}">${slot} - ${parseInt(slot) + 1}:00</option>`
            ).join('');
    }

    // Événement de changement de date
    dateInput.addEventListener('change', function() {
        if (this.value) {
            loadAvailableTimeSlots(this.value);
        }
    });

    // Initialiser le calendrier
    generateAvailabilityCalendar();
    
    // Pré-remplir si une date est déjà sélectionnée
    if (dateInput.value) {
        loadAvailableTimeSlots(dateInput.value);
    }
});
</script>

<style>
.availability-day {
    transition: all 0.3s ease;
    cursor: pointer;
}

.availability-day:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.availability-day.border-primary {
    background-color: rgba(30, 60, 114, 0.05) !important;
}
</style>
@endpush
@endsection