<?php
// app/Console/Commands/CleanupPreinscriptions.php

namespace App\Console\Commands;

use App\Models\Preinscription;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupPreinscriptions extends Command
{
    protected $signature = 'preinscriptions:cleanup {--days=30 : Supprimer les préinscriptions plus anciennes que X jours}';
    protected $description = 'Nettoyer les anciennes préinscriptions';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $count = Preinscription::where('created_at', '<', $cutoffDate)
            ->where('statut', 'rejete')
            ->delete();

        $this->info("{$count} anciennes préinscriptions rejetées supprimées.");

        // Archivage des préinscriptions validées
        $archived = Preinscription::where('created_at', '<', $cutoffDate)
            ->where('statut', 'valide')
            ->update(['is_archived' => true]);

        $this->info("{$archived} préinscriptions validées archivées.");
        
        return Command::SUCCESS;
    }
}