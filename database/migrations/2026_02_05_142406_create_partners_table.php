<?php

use App\Enum\PartnerCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('0cc_socios', function (Blueprint $table) {
            // Usamos 'ind' como clave primaria autoincremental
            $table->id('ind');
            $table->integer('acc')->index();
            $table->unsignedTinyInteger('sincro')->default(0);
            // Personal Date
            $table->integer('cedula')->unique()->nullable();
            $table->string('carnet')->nullable();
            $table->string('nombre')->nullable();
            // Contact
            $table->string('celular', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo',100)->nullable();
            $table->text('direccion')->nullable();

            $table->date('nacimiento')->nullable()->index();
            $table->date('ingreso')->nullable();

            $table->string('ocupacion')->nullable();
            // 2. Default garantizado desde el Enum
            $table->enum('categoria', array_column(PartnerCategory::cases(), 'value'))
                ->default(PartnerCategory::TITULAR->value);

            $table->integer('cobrador')->default(0)->index();
            $table->index(['acc', 'categoria'],'idx_accion_categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0cc_socios');
    }
};
