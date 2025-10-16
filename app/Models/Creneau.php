<?php
// app/Models/Creneau.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Shemas\DB;

class Creneau extends Model
{
    use HasFactory;

    protected $table = 'creneaux';


    protected $fillable = [
        'date_creneau',
        'heure_debut',
        'heure_fin',
        'capacite_max',
        'reservations',
        'est_actif',
        'notes'
    ];

    protected $casts = [
        'date_creneau' => 'date',
        'est_actif' => 'boolean',
    ];

    // SCOPES
    public function scopeDisponibles(Builder $query)
    {
        return $query->where('est_actif', true)
                    ->where('reservations', '<', \DB::raw('capacite_max'))
                    ->where('date_creneau', '>=', today());
    }

    public function scopeToday(Builder $query)
    {
        return $query->where('date_creneau', today());
    }

    public function scopeThisWeek(Builder $query)
    {
        return $query->whereBetween('date_creneau', [today(), today()->addDays(7)]);
    }

    // ACCESSORS
    public function getPlacesRestantesAttribute()
    {
        return $this->capacite_max - $this->reservations;
    }

    public function getEstCompletAttribute()
    {
        return $this->reservations >= $this->capacite_max;
    }

    public function getHeureFormateeAttribute()
    {
        $debut = Carbon::parse($this->heure_debut)->format('H:i');
        $fin = Carbon::parse($this->heure_fin)->format('H:i');
        return "{$debut} - {$fin}";
    }

    public function getDateFormateeAttribute()
    {
        return Carbon::parse($this->date_creneau)->locale('fr')->isoFormat('dddd D MMMM YYYY');
    }

    // MÉTHODES MÉTIERS
    public function reserver()
    {
        if ($this->estComplet) {
            throw new \Exception('Ce créneau est complet.');
        }

        $this->increment('reservations');
    }

    public function liberer()
    {
        if ($this->reservations > 0) {
            $this->decrement('reservations');
        }
    }

    public static function genererCreneaux($dateDebut, $dateFin)
    {
        $creneaux = [];
        $dates = Carbon::parse($dateDebut)->daysUntil($dateFin);
        
        $heures = [
            ['08:00', '09:00'],
            ['09:00', '10:00'],
            ['10:00', '11:00'],
            ['11:00', '12:00'],
            ['14:00', '15:00'],
            ['15:00', '16:00'],
            ['16:00', '17:00']
        ];

        foreach ($dates as $date) {
            // Exclure les weekends
            if (!$date->isWeekend()) {
                foreach ($heures as $heure) {
                    Creneau::firstOrCreate([
                        'date_creneau' => $date->format('Y-m-d'),
                        'heure_debut' => $heure[0],
                        'heure_fin' => $heure[1/2],
                    ], [
                        'capacite_max' => 20,
                        'est_actif' => true
                    ]);
                }
            }
        }

        return Creneau::whereBetween('date_creneau', [$dateDebut, $dateFin])->get();
    }
}