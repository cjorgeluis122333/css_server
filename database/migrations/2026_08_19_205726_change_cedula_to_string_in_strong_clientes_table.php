<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear columna temporal
        Schema::table('0cc_strong_clientes', function (Blueprint $table) {
            $table->string('cedula_temp', 20)->nullable();
        });

        // 2. Copiar los datos de la columna original a la temporal
        DB::statement('UPDATE `0cc_strong_clientes` SET `cedula_temp` = CAST(`cedula` AS CHAR)');

        // 3. Quitar la clave primaria actual
        Schema::table('0cc_strong_clientes', function (Blueprint $table) {
            $table->dropPrimary(['cedula']);
        });

        // 4. Eliminar la columna anterior y renombrar la nueva
        Schema::table('0cc_strong_clientes', function (Blueprint $table) {
            $table->dropColumn('cedula');
            $table->renameColumn('cedula_temp', 'cedula');
            $table->primary('cedula'); // Reasignar Primary Key
        });
    }

    public function down(): void
    {
        Schema::table('0cc_strong_clientes', function (Blueprint $table) {
            $table->integer('cedula_temp')->nullable();
        });

        DB::statement('UPDATE `0cc_strong_clientes` SET `cedula_temp` = CAST(`cedula` AS UNSIGNED)');

        Schema::table('0cc_strong_clientes', function (Blueprint $table) {
            $table->dropPrimary(['cedula']);
            $table->dropColumn('cedula');
            $table->renameColumn('cedula_temp', 'cedula');
            $table->primary('cedula');
        });
    }
};
