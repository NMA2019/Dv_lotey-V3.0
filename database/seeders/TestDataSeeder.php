<?php
// database/seeders/TestDataSeeder.php

namespace Database\Seeders;

use App\Models\Preinscription;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        $agent = User::where('role', 'agent')->first();

        // Créer quelques préinscriptions de test
        $preinscriptions = Preinscription::factory()->count(10)->create([
            'agent_id' => $agent->id
        ]);

        // Créer les paiements associés
        foreach ($preinscriptions as $preinscription) {
            Paiement::create([
                'preinscription_id' => $preinscription->id,
                'mode_paiement' => $this->getRandomModePaiement(),
                'reference_paiement' => 'REF' . rand(1000, 9999),
                'montant' => 5000,
                'statut' => $this->getRandomStatutPaiement(),
                'date_paiement' => $preinscription->created_at->addHours(2),
            ]);
        }

        // Tester quelques fonctionnalités
        $this->testModelFeatures();
    }

    private function getRandomModePaiement()
    {
        $modes = ['mtn', 'orange', 'espece'];
        return $modes[array_rand($modes)];
    }

    private function getRandomStatutPaiement()
    {
        $statuts = ['en_attente', 'paye', 'echec'];
        return $statuts[array_rand($statuts)];
    }

    private function testModelFeatures()
    {
        // Test des scopes et méthodes
        $stats = Preinscription::getStatsGlobales();
        $tauxTraitement = $agent->taux_traitement;
        
        $this->command->info('Stats globales: ' . json_encode($stats));
        $this->command->info("Taux de traitement de l'agent: {$tauxTraitement}%");
    }
}