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
        Schema::create('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            // Clave primaria compuesta — sin auto-increment
            $table->integer('ano_tabla')->comment('Año de la tabla original para evitar colisiones de ID');
            $table->unsignedInteger('ind');

            $table->integer('cedula')->nullable();
            $table->string('mes', 255)->nullable();
            $table->string('plan', 255)->nullable();
            $table->integer('monto');
            $table->integer('dolares');
            $table->integer('zelle');
            $table->integer('recibo');
            $table->integer('fecha')->comment('Timestamp de la transacción');
            $table->string('observacion', 255)->nullable();
            $table->string('operador', 255)->nullable();

            $table->primary(['ano_tabla', 'ind']);

            $table->index('cedula', 'idx_cedula');
            $table->index(['mes', 'fecha'], 'idx_mes_fecha');

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
        Schema::dropIfExists('0cc_ingles_pagos_unificado');
    }
};
