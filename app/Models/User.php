<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // RELATIONS
    public function preinscriptions()
    {
        return $this->hasMany(Preinscription::class, 'agent_id');
    }

    public function preinscriptionsTraitees()
    {
        return $this->preinscriptions()->whereIn('statut', ['valide', 'rejete', 'reclasse']);
    }

    public function preinscriptionsEnAttente()
    {
        return $this->preinscriptions()->where('statut', 'en_attente');
    }

    // SCOPES
    public function scopeAdmin(Builder $query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeAgent(Builder $query)
    {
        return $query->where('role', 'agent');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }

    // MÉTHODES UTILITAIRES
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAgent()
    {
        return $this->role === 'agent';
    }

    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    public function canManagePreinscriptions()
    {
        return $this->isAdmin() || $this->isAgent();
    }

    public function getRoleLabelAttribute()
    {
        $roles = [
            'admin' => 'Administrateur',
            'agent' => 'Agent'
        ];

        return $roles[$this->role] ?? $this->role;
    }

    public function getStatutLabelAttribute()
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getInitialesAttribute()
    {
        $words = explode(' ', $this->name);
        $initiales = '';
        
        foreach ($words as $word) {
            $initiales .= strtoupper(substr($word, 0, 1));
        }

        return $initiales;
    }

    // MÉTHODES MÉTIERS
    public function getPreinscriptionsCountByStatut()
    {
        return $this->preinscriptions()
            ->selectRaw('statut, count(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut')
            ->toArray();
    }

    public function getTauxTraitementAttribute()
    {
        $total = $this->preinscriptions()->count();
        $traitees = $this->preinscriptionsTraitees()->count();

        return $total > 0 ? round(($traitees / $total) * 100, 2) : 0;
    }

    // ÉVÉNEMENTS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // S'assurer que l'email est en minuscule
            $user->email = strtolower($user->email);
        });

        static::updating(function ($user) {
            // S'assurer que l'email est en minuscule
            $user->email = strtolower($user->email);
        });
    }
}