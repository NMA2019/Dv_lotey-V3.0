// public/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu mobile
    initMobileMenu();
    
    // Gestion des étapes de préinscription
    initStepProgress();
    
    // Validation des formulaires
    initFormValidation();
    
    // Animations et interactions
    initAnimations();
    
    // Dashboard interactions
    initDashboard();
});

function initMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
            this.setAttribute('aria-expanded', navbarCollapse.classList.contains('show'));
        });
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (!navbarToggler.contains(event.target) && !navbarCollapse.contains(event.target)) {
                navbarCollapse.classList.remove('show');
                navbarToggler.setAttribute('aria-expanded', 'false');
            }
        });
    }
}

function initStepProgress() {
    const steps = document.querySelectorAll('.step');
    if (steps.length === 0) return;
    
    // Mettre à jour la progression basée sur l'URL actuelle
    updateStepProgress();
    
    // Animation des étapes
    steps.forEach((step, index) => {
        step.style.animationDelay = `${index * 0.1}s`;
        step.classList.add('fade-in');
    });
}

function updateStepProgress() {
    const currentPath = window.location.pathname;
    const steps = document.querySelectorAll('.step');
    
    steps.forEach((step, index) => {
        step.classList.remove('active', 'completed');
        
        if (currentPath.includes(`etape${index + 1}`)) {
            step.classList.add('active');
            // Marquer les étapes précédentes comme complétées
            for (let i = 0; i < index; i++) {
                steps[i].classList.add('completed');
            }
        } else if (index < getCurrentStepIndex()) {
            step.classList.add('completed');
        }
    });
}

function getCurrentStepIndex() {
    const path = window.location.pathname;
    if (path.includes('etape1')) return 1;
    if (path.includes('etape2')) return 2;
    if (path.includes('etape3')) return 3;
    if (path.includes('recap')) return 4;
    return 0;
}

function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showFormErrors(this);
            }
        });
        
        // Validation en temps réel
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.getAttribute('name');
    let isValid = true;
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'Ce champ est obligatoire');
        isValid = false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Veuillez entrer une adresse email valide');
            isValid = false;
        }
    }
    
    // Phone validation
    if (fieldName === 'telephone' && value) {
        const phoneRegex = /^[+]?[0-9\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            showFieldError(field, 'Veuillez entrer un numéro de téléphone valide');
            isValid = false;
        }
    }
    
    // Date validation (minimum age)
    if (fieldName === 'date_naissance' && value) {
        const birthDate = new Date(value);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        if (age < 18) {
            showFieldError(field, 'Vous devez avoir au moins 18 ans');
            isValid = false;
        }
    }
    
    if (isValid) {
        showFieldSuccess(field);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(errorDiv);
    }
    
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function showFieldSuccess(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
}

function clearFieldError(field) {
    field.classList.remove('is-invalid', 'is-valid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function showFormErrors(form) {
    const firstInvalidField = form.querySelector('.is-invalid');
    if (firstInvalidField) {
        firstInvalidField.focus();
        
        // Scroll to the first error
        firstInvalidField.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
}

function initAnimations() {
    // Animation des cartes au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observer les cartes et sections
    document.querySelectorAll('.card, .stats-card, .hero-section').forEach(el => {
        observer.observe(el);
    });
    
    // Hover effects
    document.querySelectorAll('.card, .btn').forEach(el => {
        el.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        el.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

function initDashboard() {
    // Gestion des tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Confirmation des actions critiques
    const deleteButtons = document.querySelectorAll('.btn-delete, .btn-danger');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// Utility functions
function formatPhoneNumber(phone) {
    return phone.replace(/(\d{2})(?=\d)/g, '$1 ');
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR');
}

function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading-spinner"></span> Traitement...';
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// API calls for dashboard
async function fetchDashboardStats() {
    try {
        const response = await fetch('/admin/statistiques');
        const data = await response.json();
        updateDashboardCharts(data);
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
}

function updateDashboardCharts(stats) {
    // Implémenter la mise à jour des graphiques ici
    console.log('Updating charts with:', stats);
}

// Ajouter à public/js/script.js

// Gestion des formulaires de contact
function initContactForm() {
    const contactForm = document.querySelector('form[action*="contact"]');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
            submitBtn.disabled = true;
            
            // Réactiver après 5s au cas où
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    }
}

// Animation des cartes de prix
function initPricingCards() {
    const pricingCards = document.querySelectorAll('.pricing-card');
    pricingCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
}

// Initialiser les nouvelles fonctionnalités
document.addEventListener('DOMContentLoaded', function() {
    initContactForm();
    initPricingCards();
});