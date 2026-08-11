<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('0cc_natacion_clientes', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->id('ind');
            $table->integer('cedula');
            $table->string('nombre')->nullable();
            $table->string('nacimiento', 50)->nullable();
            $table->string('sexo', 10)->nullable();
            $table->string('socio', 50)->nullable()->default('No Socio');
            $table->text('padres')->nullable();
            $table->string('repre_cedula1', 50)->nullable();
            $table->string('repre_nombre1')->nullable();
            $table->string('repre_cedula2', 50)->nullable();
            $table->string('repre_nombre2')->nullable();
            $table->string('repre_cedula3', 50)->nullable();
            $table->string('repre_nombre3')->nullable();
            $table->string('last_pay', 50)->nullable();
            $table->string('last_pay_mont', 50)->nullable();
            $table->string('operador')->nullable();

            $table->unique('cedula', 'uq_cedula');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_natacion_clientes');
    }
};
