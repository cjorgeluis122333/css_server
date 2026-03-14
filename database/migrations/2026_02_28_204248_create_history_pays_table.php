<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('historial_pagos_separado', function (Blueprint $table) {
            // Usamos 'ind' como clave primaria autoincremental
            $table->id('ind');

            // Relación con el socio a través de la cuenta 'acc'
            $table->integer('acc')->index();

            // Detalles del tiempo y periodo
            $table->string('time', 50)->nullable();
            $table->string('fecha', 20)->nullable();
            $table->string('mes', 20)->nullable();

            // Detalles de la operación
            $table->string('oper',50)->nullable();
            $table->string('resibo',50)->nullable();
            $table->string('control',50)->nullable();
            $table->string('factura',50)->nullable();

            $table->decimal('monto', 15, 2)->default(0.00);
            $table->string('descript',100)->nullable();
            $table->string('observaciones',100)->nullable();

            // Metadatos adicionales
            $table->string('seniat', 100)->default('no');
            $table->text('operador')->nullable();

            /**
             * OPCIONAL: Definir la relación de clave foránea.
             * Asegúrate de que '0cc_socios' ya tenga el índice en 'acc'.
             */
            $table->foreign('acc')
                ->references('acc')
                ->on('0cc_socios')
                ->onDelete('cascade'); // Si se borra el socio, se borra su historial
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_pagos_separado');
    }
};

/**
 * `ind` INT UNSIGNED NOT NULL AUTO_INCREMENT,
 * `acc` INT NOT NULL,
 * `time` VARCHAR(50) DEFAULT NULL,
 * `fecha` VARCHAR(20) DEFAULT NULL,
 * `mes` VARCHAR(20) DEFAULT NULL,
 * `oper` TEXT DEFAULT NULL,
 * `monto` DECIMAL(15,2) DEFAULT 0.00,
 * `descript` TEXT DEFAULT NULL,
 * `seniat` VARCHAR(100) DEFAULT 'no',
 * `operador` TEXT DEFAULT NULL,
 * PRIMARY KEY (`ind`),
 * INDEX (`acc`)
 */
