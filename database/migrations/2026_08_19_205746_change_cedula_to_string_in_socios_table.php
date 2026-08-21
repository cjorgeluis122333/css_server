<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('0cc_socios', function (Blueprint $table) {
            $table->string('cedula_temp', 20)->nullable();
        });

        DB::statement('UPDATE `0cc_socios` SET `cedula_temp` = CAST(`cedula` AS CHAR)');

        Schema::table('0cc_socios', function (Blueprint $table) {
            $table->dropColumn('cedula');
            $table->renameColumn('cedula_temp', 'cedula');
        });
    }

    public function down(): void
    {
        Schema::table('0cc_socios', function (Blueprint $table) {
            $table->integer('cedula_temp')->nullable();
        });

        DB::statement('UPDATE `0cc_socios` SET `cedula_temp` = CAST(`cedula` AS UNSIGNED)');

        Schema::table('0cc_socios', function (Blueprint $table) {
            $table->dropColumn('cedula');
            $table->renameColumn('cedula_temp', 'cedula');
        });
    }
};
