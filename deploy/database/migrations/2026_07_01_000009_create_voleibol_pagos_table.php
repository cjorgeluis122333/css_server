<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('0cc_voleibol_pagos_unificado', function (Blueprint $table) {
            $table->increments('ind');

            $table->integer('cedula')->nullable();
            $table->string('mes', 10)->nullable();
            $table->string('plan', 50)->nullable();
            $table->integer('monto');
            $table->integer('dolares');
            $table->integer('zelle');
            $table->integer('recibo');
            $table->integer('fecha')->comment('Almacena timestamp unix');
            $table->string('observacion', 255)->nullable();
            $table->string('operador', 50)->nullable();
            $table->integer('ano_origen')->comment('Columna de control para identificar el año de la tabla origen');

            $table->index(['cedula', 'mes'], 'idx_cedula_mes');
            $table->index('fecha', 'idx_fecha');
            $table->index('mes', 'idx_mes');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('0cc_voleibol_pagos_unificado');
    }
};
