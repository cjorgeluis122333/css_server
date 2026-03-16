<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
            Schema::create('0cc_cuotas', function (Blueprint $table) {
                // 'ind' como clave primaria autoincremental
                $table->increments('ind');
                $table->char('mes', 7)->index();
                $table->decimal('cuota', 11, 2)->default(0.00);
                $table->decimal('impuesto', 10, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_cuotas');
    }
};
