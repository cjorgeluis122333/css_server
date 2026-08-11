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
        Schema::create('0cc_lever_pagos_unificado', function (Blueprint $table) {
            $table->increments('id_pago');

            $table->integer('cedula')->nullable();
            $table->string('mes', 7)->nullable()->comment('Almacena estrictamente YYYY-MM');
            $table->string('d', 10)->nullable()->comment('Almacena el código (D7, D4, S1) o NULL si no existe');
            $table->string('plan', 100)->nullable();
            $table->decimal('monto', 11, 2)->default(0.00);
            $table->decimal('dolares', 11, 2)->default(0.00);
            $table->decimal('zelle', 11, 2)->default(0.00);
            $table->integer('recibo');
            $table->integer('fecha')->comment('Almacena fecha en formato Unix Timestamp');
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
        Schema::dropIfExists('0cc_lever_pagos_unificado');
    }
};
