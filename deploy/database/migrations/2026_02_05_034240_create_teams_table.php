<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domino_equipos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nombre_completo', 255)->nullable(false);
            $table->string('abreviatura', 10)->nullable(false)->unique();
            $table->timestamp('fecha_creacion')->useCurrent()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domino_equipos');
    }
};
