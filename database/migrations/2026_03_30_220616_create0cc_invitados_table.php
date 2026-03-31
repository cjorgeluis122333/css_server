<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_invitados', function (Blueprint $table) {
            $table->id('ind'); // Crea un BIGINT UNSIGNED NOT NULL AUTO_INCREMENT como llave primaria

            $table->string('cedula', 20)->nullable()->index();

            $table->string('nombre', 150)->nullable()->index();

            // Llave foránea lógica (indexada) hacia el socio
            $table->unsignedBigInteger('acc')->nullable()->index();

            $table->integer('last_time')->unsigned()->nullable();

            $table->string('operador', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_invitados');
    }
};
