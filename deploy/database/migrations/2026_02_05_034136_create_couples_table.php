<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_2025_parejas', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('torneo_id')->nullable(false);
            $table->string('equipo', 10)->nullable(false);
            $table->string('jugador1', 20)->nullable(false);
            $table->string('jugador2', 20)->nullable(false);
            $table->boolean('activa')->default(1)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent()->nullable();

            // Foreign key
            $table->foreign('torneo_id')->references('id')->on('domino_2025_torneos')->onDelete('cascade');
            // Consider adding foreign keys for jugador1 and jugador2 if they reference domino_jugadores.cedula
            // $table->foreign('jugador1')->references('cedula')->on('domino_jugadores')->onDelete('restrict');
            // $table->foreign('jugador2')->references('cedula')->on('domino_jugadores')->onDelete('restrict');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_2025_parejas');
    }
};
