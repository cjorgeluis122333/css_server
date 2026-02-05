<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_2025_rondas', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('torneo_id')->nullable(false);
            $table->integer('numero')->nullable(false);
            $table->timestamp('fecha_creacion')->useCurrent()->nullable();

            // Foreign key
            $table->foreign('torneo_id')->references('id')->on('domino_2025_torneos')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_2025_rondas');
    }
};
