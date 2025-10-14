<?php
// database/migrations/2024_01_15_create_creneaux_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreneauxTable extends Migration
{
    public function up()
    {
        Schema::create('creneaux', function (Blueprint $table) {
            $table->id();
            $table->date('date_creneau');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('capacite_max')->default(5);
            $table->integer('reservations')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['date_creneau', 'heure_debut']);
            $table->index(['date_creneau', 'est_actif']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('creneaux');
    }
}