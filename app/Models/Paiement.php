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
        'notes'
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

    // SCOPES
    public function scopePayes(Builder $query)
    {
        return $query->where('statut', 'paye');
    }

    public function scopeEnAttente(Builder $query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeEchecs(Builder $query)
    {
        return $query->where('statut', 'echec');
    }

    public function scopeRembourses(Builder $query)
    {
        return $query->where('statut', 'rembourse');
    }

    public function scopeParMode(Builder $query, $mode)
    {
        return $query->where('mode_paiement', $mode);
    }

    public function scopeParPeriode(Builder $query, $debut, $fin)
    {
        return $query->whereBetween('created_at', [$debut, $fin]);
    }

    // ACCESSORS
    public function getModePaiementLisibleAttribute()
    {
        $modes = [
            'mtn' => 'Mobile Money (MTN)',
            'orange' => 'Mobile Money (Orange)',
            'espece' => 'Espèces'
        ];

        return $modes[$this->mode_paiement] ?? $this->mode_paiement;
    }

    public function getStatutLabelAttribute()
    {
        $statuts = [
            'en_attente' => 'En attente',
            'paye' => 'Payé',
            'echec' => 'Échec',
            'rembourse' => 'Remboursé'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    public function getStatutCssClassAttribute()
    {
        $classes = [
            'en_attente' => 'warning',
            'paye' => 'success',
            'echec' => 'danger',
            'rembourse' => 'info'
        ];

        return $classes[$this->statut] ?? 'secondary';
    }

    public function getMontantFormateAttribute()
    {
        return number_format($this->montant, 2, ',', ' ') . ' FCFA';
    }

    public function getEstPayeAttribute()
    {
        return $this->statut === 'paye';
    }

    public function getEstEnAttenteAttribute()
    {
        return $this->statut === 'en_attente';
    }

    // MÉTHODES MÉTIERS
    public function marquerCommePaye($datePaiement = null)
    {
        $this->update([
            'statut' => 'paye',
            'date_paiement' => $datePaiement ?? now()
        ]);

        return $this;
    }

    public function marquerCommeEchec($notes = null)
    {
        $this->update([
            'statut' => 'echec',
            'notes' => $notes
        ]);

        return $this;
    }

    public function marquerCommeRembourse($notes = null)
    {
        $this->update([
            'statut' => 'rembourse',
            'notes' => $notes
        ]);

        return $this;
    }

    public function peutEtreRembourse()
    {
        return $this->est_paye && !$this->est_rembourse;
    }

    public function getTarifParDefaut()
    {
        // Vous pouvez adapter cette logique selon vos besoins
        return 5000.00; // 5,000 FCFA par exemple
    }

    // ÉVÉNEMENTS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paiement) {
            // Définir le montant par défaut si non spécifié
            if (empty($paiement->montant)) {
                $paiement->montant = $paiement->getTarifParDefaut();
            }
        });
    }

    // MÉTHODES STATIQUES
    public static function getStatsParMode()
    {
        return static::selectRaw('mode_paiement, count(*) as count, sum(montant) as total')
            ->where('statut', 'paye')
            ->groupBy('mode_paiement')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->mode_paiement => [
                        'count' => $item->count,
                        'total' => $item->total
                    ]
                ];
            })
            ->toArray();
    }

    public static function getChiffreAffairesMensuel()
    {
        return static::where('statut', 'paye')
            ->whereYear('date_paiement', now()->year)
            ->whereMonth('date_paiement', now()->month)
            ->sum('montant');
    }
}