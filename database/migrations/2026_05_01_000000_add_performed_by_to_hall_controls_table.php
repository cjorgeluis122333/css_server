<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('0cc_salones_control_unificado', 'performed_by')) {
            Schema::table('0cc_salones_control_unificado', function (Blueprint $table) {
                $table->foreignId('performed_by')->nullable()->after('hora')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('0cc_salones_control_unificado', 'performed_by')) {
            Schema::table('0cc_salones_control_unificado', function (Blueprint $table) {
                $table->dropForeign(['performed_by']);
                $table->dropColumn('performed_by');
            });
        }
    }
};
