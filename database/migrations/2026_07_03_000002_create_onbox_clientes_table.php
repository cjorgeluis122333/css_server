<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('0cc_onbox_clientes', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->id('ind');
            $table->bigInteger('cedula');
            $table->tinyText('nombre')->nullable();
            $table->tinyText('nacimiento');
            $table->tinyText('sexo')->nullable();
            $table->string('socio')->nullable()->default('No Socio');
            $table->text('padres');
            $table->tinyText('last_pay')->nullable();
            $table->tinyText('last_pay_mont')->nullable();
            $table->tinyText('d')->nullable();
            $table->tinyText('operador');

            $table->unique('cedula', 'idx_cedula_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_onbox_clientes');
    }
};
