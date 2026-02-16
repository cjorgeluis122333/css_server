<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_socios', function (Blueprint $table) {
            // Usamos 'ind' como clave primaria autoincremental
            $table->id('ind');

            $table->integer('sincro')->default(0);
            $table->integer('acc')->index();
            $table->integer('cedula')->unique()->nullable();
            $table->string('carnet')->nullable();
            $table->string('nombre')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->text('direccion')->nullable();

            $table->date('nacimiento')->nullable()->index();
            $table->string('ingreso')->nullable();

            $table->string('ocupacion')->nullable();
            $table->string('categoria', 30)->default('titular')->index();
            $table->integer('cobrador')->default(0)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_socios');
    }
};
