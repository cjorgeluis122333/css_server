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
            $table->unsignedTinyInteger('sincro')->default(0);
            $table->integer('acc')->index();
            // Personal Date
            $table->integer('cedula')->nullable();
            $table->string('carnet')->nullable();
            $table->string('nombre')->nullable();
            // Contact
            $table->string('celular', 100)->nullable();
            $table->string('telefono', 100)->nullable();
            $table->string('correo',150)->nullable();
            $table->text('direccion')->nullable();

            $table->string('nacimiento',50)->nullable()->index();
            $table->string('ingreso',50)->nullable();

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
