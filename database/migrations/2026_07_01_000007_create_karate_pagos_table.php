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
        Schema::create('0cc_karate_pagos', function (Blueprint $table) {
            $table->increments('ind');

            $table->integer('cedula')->nullable();
            $table->string('mes', 7)->nullable();
            $table->string('plan', 50)->nullable();
            $table->integer('monto')->default(0);
            $table->integer('dolares')->default(0);
            $table->integer('zelle')->default(0);
            $table->integer('recibo')->default(0);
            $table->integer('fecha');
            $table->string('observacion', 255)->nullable();
            $table->string('operador', 50)->nullable();

            $table->index('cedula', 'idx_cedula');
            $table->index('mes', 'idx_mes');
            $table->index('recibo', 'idx_recibo');
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
        Schema::dropIfExists('0cc_karate_pagos');
    }
};
