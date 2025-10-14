<?php

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
            $table->enum('mode_paiement', ['mtn', 'orange', 'espece']);
            $table->string('reference_paiement')->nullable();
            $table->decimal('montant', 10, 2);
            $table->enum('statut', ['en_attente', 'paye', 'echec', 'rembourse'])->default('en_attente');
            $table->dateTime('date_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paiements');
    }
}