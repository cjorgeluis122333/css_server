<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable()->change();
            $table->string('recibo', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            $table->integer('cedula')->nullable()->change();
            $table->integer('recibo')->change();
        });
    }
};
