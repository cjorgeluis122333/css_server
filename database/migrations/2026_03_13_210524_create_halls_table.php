<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_salones_precios', function (Blueprint $table) {
            $table->id("ind");
            $table->string("salon");
            $table->double("socio");  //Cost for member
            $table->double("no_socio"); // Cost for not member
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_salones_precios');
    }
};
