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
        Schema::create('0cc_pinpon_pagos_unificada', function (Blueprint $table) {
            // Clave primaria compuesta — sin auto-increment
            $table->unsignedInteger('ind_original')->comment('ID original de la tabla anual');
            $table->integer('anio_origen')->comment('Año de la tabla de la cual proviene el dato');

            $table->integer('cedula')->nullable();
            $table->string('mes', 7)->nullable()->comment('Almacena estrictamente YYYY-MM');
            $table->string('d', 10)->nullable()->comment('Almacena el código (D7, D4, S1) o NULL si no existe');
            $table->string('plan', 50)->nullable();
            $table->integer('monto');
            $table->integer('dolares');
            $table->integer('zelle');
            $table->integer('recibo');
            $table->integer('fecha')->comment('Unix Timestamp');
            $table->string('observacion', 255)->nullable();
            $table->string('operador', 50)->nullable();

            $table->primary(['anio_origen', 'ind_original']);

            $table->index(['cedula', 'mes'], 'idx_cedula_mes');
            $table->index('fecha', 'idx_fecha');
            $table->index('recibo', 'idx_recibo');

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
        Schema::dropIfExists('0cc_pinpon_pagos_unificada');
    }
};
