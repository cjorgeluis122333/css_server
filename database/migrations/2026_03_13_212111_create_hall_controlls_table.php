<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_salones_control_unificado', function (Blueprint $table) {
            $table->id('ind');

            // Mejoramos 'tinytext' a 'date' para que el índice sea ultra rápido
            $table->date('fecha')->nullable()->index('idx_fecha');

            // Convertimos tinytext a string (VARCHAR) para permitir indexación futura y ahorrar espacio
            $table->string('salon', 30);
            $table->integer('acc')->nullable();
            $table->string('nombre', 50)->nullable();

            $table->decimal('abono', 11, 2)->nullable();
            $table->decimal('pago', 11, 2)->nullable();
            $table->integer('pases')->nullable();

            // 'hora' como tipo time para facilitar cálculos de tiempo después
            $table->string('hora',50)->nullable();

            // Índices adicionales sugeridos para evitar lentitud en tablas grandes
            $table->index(['salon', 'fecha']); // Índice compuesto si sueles filtrar por salón y fecha
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_salones_control_unificado');
    }
};
