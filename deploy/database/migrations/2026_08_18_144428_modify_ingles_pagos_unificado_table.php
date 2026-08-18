    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Support\Facades\DB;

    return new class extends Migration
    {
        public function up(): void
        {
            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado DROP PRIMARY KEY'
            );

            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
            );

            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado DROP COLUMN ind'
            );
        }

        public function down(): void
        {
            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado ADD COLUMN ind INT UNSIGNED NOT NULL'
            );

            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado DROP PRIMARY KEY'
            );

            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado DROP COLUMN id'
            );

            DB::statement(
                'ALTER TABLE 0cc_ingles_pagos_unificado ADD PRIMARY KEY (ano_tabla, ind)'
            );
        }
    };
