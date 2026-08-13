<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('0cc_almaflamenca_pagos_unificada', function (Blueprint $table) {
            $table->dropColumn('ind_original');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('0cc_almaflamenca_pagos_unificada', function (Blueprint $table) {
            $table->unsignedInteger('ind_original')
                ->after('id_pago')
                ->comment('ID que tenía en su tabla era innecesario');
        });
    }
};
