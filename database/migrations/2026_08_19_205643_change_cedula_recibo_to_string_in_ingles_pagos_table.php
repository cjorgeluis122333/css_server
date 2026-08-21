<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            $table->string('cedula_temp', 20)->nullable();
            $table->string('recibo_temp', 20)->nullable();
        });

        DB::statement('UPDATE `0cc_ingles_pagos_unificado` SET `cedula_temp` = CAST(`cedula` AS CHAR), `recibo_temp` = CAST(`recibo` AS CHAR)');

        Schema::table('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'recibo']);
            $table->renameColumn('cedula_temp', 'cedula');
            $table->renameColumn('recibo_temp', 'recibo');
        });
    }

    public function down(): void
    {
        Schema::table('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            $table->integer('cedula_temp')->nullable();
            $table->integer('recibo_temp')->nullable();
        });

        DB::statement('UPDATE `0cc_ingles_pagos_unificado` SET `cedula_temp` = CAST(`cedula` AS UNSIGNED), `recibo_temp` = CAST(`recibo` AS UNSIGNED)');

        Schema::table('0cc_ingles_pagos_unificado', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'recibo']);
            $table->renameColumn('cedula_temp', 'cedula');
            $table->renameColumn('recibo_temp', 'recibo');
        });
    }
};
