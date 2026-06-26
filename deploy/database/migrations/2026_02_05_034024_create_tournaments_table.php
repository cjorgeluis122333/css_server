<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_2025_torneos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nombre', 255)->nullable(false);
            $table->string('sede', 10)->nullable(false);
            $table->date('fecha')->nullable(false);
            $table->json('equipos_participantes')->nullable();
            $table->boolean('finalizado')->default(0)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_2025_torneos');
    }
};
