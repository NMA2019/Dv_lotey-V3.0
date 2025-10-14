<?php
// app/Exports/PreinscriptionsExport.php

namespace App\Exports;

use App\Models\Preinscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PreinscriptionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $preinscriptions;

    public function __construct($preinscriptions)
    {
        $this->preinscriptions = $preinscriptions;
    }

    public function collection()
    {
        return $this->preinscriptions;
    }

    public function headings(): array
    {
        return [
            'N° Dossier',
            'Nom',
            'Prénom',
            'Date Naissance',
            'Nationalité',
            'Email',
            'Téléphone',
            'Ville',
            'Mode Paiement',
            'Référence Paiement',
            'Date Rendez-vous',
            'Heure Rendez-vous',
            'Statut',
            'Date Création'
        ];
    }

    public function map($preinscription): array
    {
        return [
            $preinscription->numero_dossier,
            $preinscription->nom,
            $preinscription->prenom,
            $preinscription->date_naissance->format('d/m/Y'),
            $preinscription->nationalite,
            $preinscription->email,
            $preinscription->telephone,
            $preinscription->ville,
            $preinscription->paiement->mode_paiement_lisible ?? 'N/A',
            $preinscription->paiement->reference_paiement ?? 'N/A',
            $preinscription->date_rendez_vous->format('d/m/Y'),
            $preinscription->heure_rendez_vous,
            $preinscription->statut_label,
            $preinscription->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style de l'en-tête
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e3c72']]
            ],
        ];
    }
}