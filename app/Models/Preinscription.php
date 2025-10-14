<?php
// app/Models/Preinscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Preinscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_dossier',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'email',
        'telephone',
        'adresse',
        'ville',
        'pays',
        'date_rendez_vous',
        'heure_rendez_vous',
        'statut',
        'commentaire_agent',
        'agent_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_rendez_vous' => 'date',
    ];

    // RELATIONS
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class);
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

    public function scopeReclasses(Builder $query)
    {
        return $query->where('statut', 'reclasse');
    }

    public function scopeTraitees(Builder $query)
    {
        return $query->whereIn('statut', ['valide', 'rejete', 'reclasse']);
    }

    public function scopeNonTraitees(Builder $query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopePourAujourdhui(Builder $query)
    {
        return $query->whereDate('date_rendez_vous', today());
    }

    public function scopePourLaSemaine(Builder $query)
    {
        return $query->whereBetween('date_rendez_vous', [today(), today()->addDays(7)]);
    }

    public function scopeParPeriode(Builder $query, $debut, $fin)
    {
        return $query->whereBetween('created_at', [$debut, $fin]);
    }

    public function scopeRecents(Builder $query, $jours = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    // ACCESSORS
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_naissance)->age;
    }

    public function getDateRendezVousCompleteAttribute()
    {
        return $this->date_rendez_vous->format('d/m/Y') . ' à ' . $this->heure_rendez_vous;
    }

    public function getStatutLabelAttribute()
    {
        $statuts = [
            'en_attente' => 'En attente',
            'valide' => 'Validée',
            'rejete' => 'Rejetée',
            'reclasse' => 'Reclassée'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    public function getStatutCssClassAttribute()
    {
        $classes = [
            'en_attente' => 'warning',
            'valide' => 'success',
            'rejete' => 'danger',
            'reclasse' => 'info'
        ];

        return $classes[$this->statut] ?? 'secondary';
    }

    public function getEstTraiteAttribute()
    {
        return $this->statut !== 'en_attente';
    }

    public function getEstValideAttribute()
    {
        return $this->statut === 'valide';
    }

    // MUTATORS
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = strtoupper($value);
    }

    public function setPrenomAttribute($value)
    {
        $this->attributes['prenom'] = ucwords(strtolower($value));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // MÉTHODES MÉTIERS
    public function peutEtreModifie()
    {
        return $this->statut === 'en_attente';
    }

    public function peutEtreValide()
    {
        return $this->statut === 'en_attente' && $this->paiement && $this->paiement->estPaye();
    }

    public function valider($agentId, $commentaire = null)
    {
        return $this->update([
            'statut' => 'valide',
            'agent_id' => $agentId,
            'commentaire_agent' => $commentaire
        ]);
    }

    public function rejeter($agentId, $commentaire = null)
    {
        return $this->update([
            'statut' => 'rejete',
            'agent_id' => $agentId,
            'commentaire_agent' => $commentaire
        ]);
    }

    public function reclasser($agentId, $commentaire = null)
    {
        return $this->update([
            'statut' => 'reclasse',
            'agent_id' => $agentId,
            'commentaire_agent' => $commentaire
        ]);
    }

    public function mettreEnAttente($commentaire = null)
    {
        return $this->update([
            'statut' => 'en_attente',
            'commentaire_agent' => $commentaire
        ]);
    }

    // ÉVÉNEMENTS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($preinscription) {
            // Générer le numéro de dossier automatiquement
            if (empty($preinscription->numero_dossier)) {
                $preinscription->numero_dossier = static::genererNumeroDossier();
            }
        });
    }

    // MÉTHODES STATIQUES
    public static function genererNumeroDossier()
    {
        $prefix = 'DV';
        $date = now()->format('ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function getStatsGlobales()
    {
        return [
            'total' => static::count(),
            'en_attente' => static::enAttente()->count(),
            'validees' => static::valides()->count(),
            'rejetees' => static::rejetes()->count(),
            'reclasses' => static::reclasses()->count(),
        ];
    }

    public static function getStatsMensuelles()
    {
        return static::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('statut, count(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();
    }
}