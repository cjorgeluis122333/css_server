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
        Schema::create('0cc_batting_pagos_unificada', function (Blueprint $table) {
            $table->increments('ind');

            $table->integer('cedula')->nullable();
            $table->string('mes', 7)->nullable()->comment('Almacena estrictamente YYYY-MM');
            $table->string('d', 10)->nullable()->comment('Almacena el código (D7, D4, S1) o NULL si no existe');
            $table->string('plan', 50)->nullable();
            $table->integer('monto');
            $table->integer('dolares');
            $table->integer('zelle');
            $table->integer('recibo');
            $table->integer('fecha')->comment('Timestamp de la transacción');
            $table->string('observacion', 255)->nullable();
            $table->string('operador', 50)->nullable();

            $table->index('cedula', 'idx_cedula');
            $table->index(['mes', 'd'], 'idx_mes_d');
            $table->index('fecha', 'idx_fecha');

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
        Schema::dropIfExists('0cc_batting_pagos_unificada');
    }
};
