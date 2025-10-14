<?php
// database/migrations/2024_01_15_create_paiements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaiementsTable extends Migration
{
    public function up()
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preinscription_id')->constrained()->onDelete('cascade');
            $table->enum('mode_paiement', ['mtn', 'orange', 'espece', 'wave', 'carte']);
            $table->string('reference_paiement')->nullable();
            $table->decimal('montant', 10, 2);
            $table->enum('statut', ['en_attente', 'valide', 'rejete', 'rembourse']);
            $table->dateTime('date_paiement')->nullable();
            $table->text('commentaire')->nullable();
            $table->string('preuve_paiement')->nullable(); // Chemin vers le fichier
            $table->foreignId('agent_id')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['statut', 'created_at']);
            $table->unique('reference_paiement');
        });
    }

    public function down()
    {
        Schema::dropIfExists('paiements');
    }
}