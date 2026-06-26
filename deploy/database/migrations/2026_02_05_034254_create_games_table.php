<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_2025_partidos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('ronda_id')->nullable(false);
            $table->integer('pareja1_id')->nullable(false);
            $table->integer('pareja2_id')->nullable(false);
            $table->integer('puntos1')->default(0);
            $table->integer('puntos2')->default(0);
            $table->boolean('terminado_tiempo')->default(0);
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate()->nullable();

            // Foreign keys
            $table->foreign('ronda_id')->references('id')->on('domino_2025_rondas')->onDelete('cascade');
            $table->foreign('pareja1_id')->references('id')->on('domino_2025_parejas')->onDelete('cascade');
            $table->foreign('pareja2_id')->references('id')->on('domino_2025_parejas')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_2025_partidos');
    }
};
