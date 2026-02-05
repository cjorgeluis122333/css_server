<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_jugadores', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('cedula', 20)->nullable(false);
            $table->string('nombre_completo', 255)->nullable(false);
            $table->string('equipo_abreviatura', 10)->nullable(false);
            $table->timestamp('fecha_registro')->useCurrent()->nullable();

            // Foreign key
            $table->foreign('equipo_abreviatura')->references('abreviatura')->on('domino_equipos')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_jugadores');
    }
};
