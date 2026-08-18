<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada DROP PRIMARY KEY');

        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada DROP COLUMN ind_original');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada ADD COLUMN ind_original INT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada DROP PRIMARY KEY');

        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada DROP COLUMN id');

        DB::statement('ALTER TABLE 0cc_pinpon_pagos_unificada ADD PRIMARY KEY (anio_origen, ind_original)');
    }
};
