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
        Schema::create('0cc_onbox_pagos_all', function (Blueprint $table) {
            $table->increments('ind');

            $table->unsignedInteger('cedula')->nullable();

            $table->string('mes', 7)->nullable()->comment('Almacena estrictamente YYYY-MM');
            $table->string('d', 10)->nullable()->comment('Almacena el código (D7, D4, S1) o NULL si no existe');

            $table->string('plan', 50)->nullable();
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->decimal('dolares', 10, 2)->default(0.00);
            $table->decimal('zelle', 10, 2)->default(0.00);
            $table->unsignedInteger('recibo')->default(0);
            $table->unsignedInteger('fecha')->comment('Unix Timestamp');
            $table->text('observacion')->nullable();
            $table->string('operador', 50)->nullable();

            $table->index('mes', 'idx_mes');
            $table->index(['mes', 'd'], 'idx_mes_d');
            $table->index('cedula', 'idx_cedula');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('0cc_onbox_pagos_all');
    }
};
