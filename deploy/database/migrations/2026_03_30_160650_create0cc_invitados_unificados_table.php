<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_invitados_unificados', function (Blueprint $table) {
            $table->increments('ind'); // PK
            $table->string('cedula',20)->nullable();
            // tinytext en MySQL equivale a un string de máximo 255 caracteres
            $table->string('nombre', 255)->nullable();
            $table->date('fecha')->nullable();
            $table->integer('acc')->nullable();
            $table->string('fuente', 100)->nullable();
            $table->string('operador', 100)->nullable();

            // --- ÍNDICES DE ALTO RENDIMIENTO ---

            // Índice para agrupar/filtrar reportes generales por mes
            $table->index('fecha');

            // Índice compuesto para validar: "Máximo 12 invitaciones por socio al mes"
            // Optimiza queries como: where('acc', $acc)->whereMonth('fecha', $mes)
            $table->index(['acc', 'fecha'], 'idx_acc_fecha');

            // Índice compuesto para validar: "Máximo 4 visitas por invitado al mes"
            // Optimiza queries como: where('cedula', $ced)->whereMonth('fecha', $mes)
            $table->index(['cedula', 'fecha'], 'idx_cedula_fecha');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_invitados_unificados');
    }
};
