<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_directivos_juntas', function (Blueprint $table) {
            $table->unsignedInteger('year')->primary(); // El año es el identificador único

            // Definimos las columnas para cada cargo
            // Usamos 'cedula' como referencia según tu lógica
            $table->integer('presidente')->nullable();
            $table->integer('vicepresidente')->nullable();
            $table->integer('secretario')->nullable();
            $table->integer('vicesecretario')->nullable();
            $table->integer('tesorero')->nullable();
            $table->integer('vicetesorero')->nullable();
            $table->integer('bibliotecario')->nullable();
            $table->integer('actas')->nullable();
            $table->integer('viceactas')->nullable();
            $table->integer('actos')->nullable();
            $table->integer('deportes')->nullable();
            $table->integer('vocal1')->nullable();
            $table->integer('vocal2')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_directivos_juntas');
    }
};
