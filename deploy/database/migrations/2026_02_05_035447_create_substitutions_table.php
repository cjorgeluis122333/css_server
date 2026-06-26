<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_2025_sustituciones', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('torneo_id')->nullable(false);
            $table->integer('pareja_id')->nullable(false);
            $table->string('jugador_saliente', 20)->nullable(false);
            $table->string('jugador_entrante', 20)->nullable(false);
            $table->integer('ronda')->nullable(false);
            $table->timestamp('fecha_sustitucion')->useCurrent()->nullable();
            $table->boolean('activa')->default(1)->nullable();

            // Foreign keys
            $table->foreign('torneo_id')->references('id')->on('domino_2025_torneos')->onDelete('cascade');
            $table->foreign('pareja_id')->references('id')->on('domino_2025_parejas')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_2025_sustituciones');
    }
};
