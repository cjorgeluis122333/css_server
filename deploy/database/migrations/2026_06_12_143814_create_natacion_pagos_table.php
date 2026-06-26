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
        Schema::create('0cc_natacion_pagos', function (Blueprint $table) {
            // primary key 'ind' en lugar de 'id'
            $table->increments('ind');

            $table->integer('cedula')->nullable();
            $table->integer('anio'); // Columna clave para identificar el origen tras la unificación

            // Usamos text o string corto ya que TINYTEXT equivale a VARCHAR(255) en la práctica de Laravel
            $table->string('mes', 255)->nullable();
            $table->string('plan', 255)->nullable();

            $table->integer('monto');
            $table->integer('dolares');
            $table->integer('zelle');
            $table->integer('recibo');
            $table->integer('fecha'); // Mantengo integer según tu diseño (ej. timestamp o AAAAMMDD)

            $table->string('observacion', 255)->nullable();
            $table->string('operador', 255)->nullable();

            // Índices para optimizar la velocidad con millones de filas
            $table->index(['cedula', 'anio'], 'idx_cedula_anio');
            $table->index('fecha', 'idx_fecha');

            // Configuración del motor y charset nativo de tu SQL
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
        Schema::dropIfExists('0cc_natacion_pagos');
    }
};
