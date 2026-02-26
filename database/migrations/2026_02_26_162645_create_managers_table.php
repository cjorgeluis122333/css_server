<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_directivos_datos', function (Blueprint $table) {
            $table->id('ind');
            $table->integer('cedula')->unique()->nullable();
            $table->string('nombre')->nullable();
            $table->integer('acc')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_directivos_datos');
    }
};
