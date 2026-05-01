# Migration commands

## Create a command

```shell
php artisan make:command MigratePartnersToUsers
```

## Execute a custom command

```shell
php artisan app:migrate-partners-to-users
```

## How look a command

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enum\UserRole;
use App\Enum\PartnerCategory;

class MigratePartnersToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-partners-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra socios titulares a la tabla de usuarios aplicando reglas de negocio';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando la migración de socios a usuarios...');

        // 1. Construimos la consulta con todas tus restricciones
        $query = DB::table('0cc_socios')
            ->where('categoria', PartnerCategory::TITULAR->value) // Solo titulares
            ->whereNotNull('cedula')                              // Cédula no nula
            ->where('cedula', '!=', 0)                            // Cédula no sea 0
            ->whereNotNull('correo')                              // Correo no nulo
            ->where('correo', '!=', '')                           // Correo no vacío
            ->where('correo', 'NOT LIKE', '@%');                  // Correo que no empiece con @ (ej: @hotmail.com)

        $totalSocios = $query->count();
        
        if ($totalSocios === 0) {
            $this->warn('No se encontraron socios que cumplan con los criterios.');
            return;
        }

        $this->info("Se encontraron {$totalSocios} socios válidos. Procesando...");
        $bar = $this->output->createProgressBar($totalSocios);

        $insertedCount = 0;

        // 2. Usamos chunk para procesar en bloques de 500 y no saturar la memoria (RAM)
        $query->orderBy('ind')->chunk(500, function ($socios) use ($bar, &$insertedCount) {
            $usersToInsert = [];
            $now = now();

            foreach ($socios as $socio) {
                // Generamos el rol usando la misma lógica que tienes en tu AuthController
                $assignedRole = UserRole::fromAcc($socio->acc);

                $usersToInsert[] = [
                    'acc'        => $socio->acc,
                    'cedula'     => $socio->cedula,
                    'correo'     => $socio->correo,
                    // Hasheamos la cédula para que sea la contraseña inicial
                    'password'   => Hash::make((string) $socio->cedula),
                    'role'       => $assignedRole->value, 
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // 3. InsertOrIgnore: Inserción masiva. Si el correo o ACC ya existen en la tabla users, se ignoran para no romper el script
            $inserted = DB::table('users')->insertOrIgnore($usersToInsert);
            $insertedCount += $inserted;

            $bar->advance(count($socios));
        });

        $bar->finish();
        $this->newLine();
        $this->info("¡Proceso completado! Se insertaron {$insertedCount} usuarios nuevos exitosamente.");
    }
}
```
