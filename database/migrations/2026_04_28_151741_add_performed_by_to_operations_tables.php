<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_pagos_separado', function (Blueprint $table) {
            $table->unsignedBigInteger('performed_by')->nullable()->after('operador');
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('0cc_salones_control_unificado', function (Blueprint $table) {
            $table->unsignedBigInteger('performed_by')->nullable()->after('hora');
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('0cc_invitados_unificados', function (Blueprint $table) {
            $table->unsignedBigInteger('performed_by')->nullable()->after('operador');
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('historial_pagos_separado', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn('performed_by');
        });

        Schema::table('0cc_salones_control_unificado', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn('performed_by');
        });

        Schema::table('0cc_invitados_unificados', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn('performed_by');
        });
    }
};
