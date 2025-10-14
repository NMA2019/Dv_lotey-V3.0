<?php
// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'preinscription_id',
        'mode_paiement',
        'reference_paiement',
        'montant',
        'statut',
        'date_paiement',
        'commentaire',
        'preuve_paiement',
        'agent_id'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
    ];

    // RELATIONS
    public function preinscription()
    {
        return $this->belongsTo(Preinscription::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // SCOPES
    public function scopeEnAttente(Builder $query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValides(Builder $query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeRejetes(Builder $query)
    {
        return $query->where('statut', 'rejete');
    }

    public function scopeToday(Builder $query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    // ACCESSORS
    public function getModePaiementLisibleAttribute()
    {
        $modes = [
            'mtn' => 'MTN Mobile Money',
            'orange' => 'Orange Money',
            'espece' => 'Espèces',
            'wave' => 'Wave',
            'carte' => 'Carte Bancaire'
        ];

        return $modes[$this->mode_paiement] ?? $this->mode_paiement;
    }

    public function getStatutLabelAttribute()
    {
        $statuts = [
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            'rembourse' => 'Remboursé'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    public function getStatutCssClassAttribute()
    {
        $classes = [
            'en_attente' => 'warning',
            'valide' => 'success',
            'rejete' => 'danger',
            'rembourse' => 'info'
        ];

        return $classes[$this->statut] ?? 'secondary';
    }

    public function getMontantFormateAttribute()
    {
        return number_format($this->montant, 0, ',', ' ') . ' FCFA';
    }

    // MÉTHODES MÉTIERS
    public function valider($agentId, $commentaire = null)
    {
        $this->update([
            'statut' => 'valide',
            'agent_id' => $agentId,
            'date_paiement' => now(),
            'commentaire' => $commentaire
        ]);

        // Mettre à jour le statut de la préinscription
        $this->preinscription->update(['statut' => 'valide']);
    }

    public function rejeter($agentId, $commentaire)
    {
        $this->update([
            'statut' => 'rejete',
            'agent_id' => $agentId,
            'commentaire' => $commentaire
        ]);
    }

    public function estValide()
    {
        return $this->statut === 'valide';
    }

    public function estEnAttente()
    {
        return $this->statut === 'en_attente';
    }
}