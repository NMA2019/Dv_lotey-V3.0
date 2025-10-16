<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Préinscriptions - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filtres {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #343a40;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        .table td {
            padding: 6px;
            border: 1px solid #dee2e6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DV LOTEY USA - GREEN CARD</h1>
        <h2>RAPPORT DES PRÉINSCRIPTIONS</h2>
        <p>Export généré le: {{ $date_export }}</p>
        
        @if(!empty($filtres))
        <div class="filtres">
            <strong>Filtres appliqués:</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @if(isset($filtres['statut']))
                <li>Statut: {{ $filtres['statut'] }}</li>
                @endif
                @if(isset($filtres['date_debut']) && isset($filtres['date_fin']))
                <li>Période: du {{ \Carbon\Carbon::parse($filtres['date_debut'])->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($filtres['date_fin'])->format('d/m/Y') }}</li>
                @endif
            </ul>
        </div>
        @endif
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>N° Dossier</th>
                <th>Nom Complet</th>
                <th>Date Naissance</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date Inscription</th>
                <th>Rendez-vous</th>
                <th>Statut</th>
                <th>Agent</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($preinscriptions as $preinscription)
            <tr>
                <td>{{ $preinscription->numero_dossier }}</td>
                <td>{{ $preinscription->nom }} {{ $preinscription->prenom }}</td>
                <td>{{ $preinscription->date_naissance->format('d/m/Y') }}</td>
                <td>{{ $preinscription->email }}</td>
                <td>{{ $preinscription->telephone }}</td>
                <td>{{ $preinscription->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $preinscription->date_rendez_vous->format('d/m/Y') }} à {{ $preinscription->heure_rendez_vous }}</td>
                <td class="text-center">
                    @if($preinscription->statut === 'valide')
                    <span class="badge badge-success">VALIDÉ</span>
                    @elseif($preinscription->statut === 'en_attente')
                    <span class="badge badge-warning">EN ATTENTE</span>
                    @elseif($preinscription->statut === 'rejete')
                    <span class="badge badge-danger">REJETÉ</span>
                    @else
                    <span class="badge badge-info">{{ strtoupper($preinscription->statut) }}</span>
                    @endif
                </td>
                <td>{{ $preinscription->agent->name ?? 'N/A' }}</td>
                <td class="text-right">
                    {{ $preinscription->paiement ? number_format($preinscription->paiement->montant, 0, ',', ' ') . ' FCFA' : 'N/A' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Aucune préinscription trouvée</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Récapitulatif:</strong>
        <div class="row" style="display: flex; justify-content: space-between; margin-top: 10px;">
            <div>
                <strong>Total dossiers:</strong> {{ $preinscriptions->count() }}
            </div>
            <div>
                <strong>Validés:</strong> {{ $preinscriptions->where('statut', 'valide')->count() }}
            </div>
            <div>
                <strong>En attente:</strong> {{ $preinscriptions->where('statut', 'en_attente')->count() }}
            </div>
            <div>
                <strong>Rejetés:</strong> {{ $preinscriptions->where('statut', 'rejete')->count() }}
            </div>
        </div>
        
        @php
            $totalMontant = 0;
            foreach($preinscriptions as $preinscription) {
                if($preinscription->paiement && $preinscription->paiement->statut === 'valide') {
                    $totalMontant += $preinscription->paiement->montant;
                }
            }
        @endphp
        
        <div style="margin-top: 10px;">
            <strong>Chiffre d'affaires total:</strong> {{ number_format($totalMontant, 0, ',', ' ') }} FCFA
        </div>
    </div>

    <div class="footer">
        <p>DV LOTEY USA - Green Card | Email: contact@dvlotey.com | Tél: (+237) 679 449 165</p>
        <p>Document généré automatiquement - Confidential</p>
        <p>Page 1/1</p>
    </div>
</body>
</html>