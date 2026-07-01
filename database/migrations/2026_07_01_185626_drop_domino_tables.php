<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tables with foreign keys first (order matters)
        Schema::dropIfExists('domino_2025_sustituciones');
        Schema::dropIfExists('domino_2025_partidos');
        Schema::dropIfExists('domino_2025_rondas');
        Schema::dropIfExists('domino_2025_parejas');
        Schema::dropIfExists('domino_jugadores');
        Schema::dropIfExists('domino_equipos');
        Schema::dropIfExists('domino_2025_torneos');
    }

    public function down(): void
    {
        //
    }
};
