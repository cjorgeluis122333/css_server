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
        Schema::create('0cc_almaflamenca_pagos_unificada', function (Blueprint $table) {
            $table->increments('id_pago');

            $table->unsignedInteger('ind_original')->nullable()->comment('Guarda el ind de la tabla vieja por seguridad');
            $table->integer('cedula')->nullable();
            $table->string('mes', 7)->nullable();
            $table->string('plan', 50)->nullable();
            $table->integer('monto');
            $table->integer('dolares');
            $table->integer('zelle');
            $table->integer('recibo');
            $table->integer('fecha')->comment('Almacena el Timestamp Unix original');
            $table->text('observacion')->nullable();
            $table->string('operador', 50)->nullable();

            $table->index('cedula', 'idx_cedula');
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
        Schema::dropIfExists('0cc_almaflamenca_pagos_unificada');
    }
};
