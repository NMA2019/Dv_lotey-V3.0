<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreinscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('preinscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_dossier')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('nationalite');
            $table->string('email');
            $table->string('telephone');
            $table->text('adresse');
            $table->string('ville');
            $table->string('pays');
            
            // Informations de rendez-vous
            $table->date('date_rendez_vous');
            $table->time('heure_rendez_vous');
            
            // Statut et traitement
            $table->enum('statut', ['en_attente', 'valide', 'rejete', 'reclasse'])->default('en_attente');
            $table->text('commentaire_agent')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('preinscriptions');
    }
}