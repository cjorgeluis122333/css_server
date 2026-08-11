<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('0cc_natacion_clientes', function (Blueprint $table): void {
            $table->string('repre_cedula1', 50)->nullable()->change();
            $table->string('repre_cedula2', 50)->nullable()->change();
            $table->string('repre_cedula3', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('0cc_natacion_clientes', function (Blueprint $table): void {
            $table->integer('repre_cedula1')->nullable()->change();
            $table->integer('repre_cedula2')->nullable()->change();
            $table->integer('repre_cedula3')->nullable()->change();
        });
    }
};
