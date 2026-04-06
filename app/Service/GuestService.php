<?php

namespace App\Service;

use App\Models\Guest;
use App\Models\RegisteredGuest;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Collection;

class GuestService
{
    /**
     * Inserta un nuevo invitado validando las reglas de negocio y
     * sincronizando el catálogo de invitados registrados.
     *
     * @throws Exception Si se superan los límites permitidos o falla la BD.
     */
    public function createGuest(array $data): Guest
    {
        $fecha = Carbon::parse($data['fecha']);
        $mes = $fecha->month;
        $year = $fecha->year;

        // 1. Validar límite del socio (Máximo 24 al mes)
        $invitacionesSocio = Guest::where('acc', $data['acc'])
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $year)
            ->count();

        if ($invitacionesSocio >= 24) {
            throw new Exception('El socio titular ya ha alcanzado el límite de 24 invitaciones para este mes.', 422);
        }

        // 2. Validar límite del invitado (Máximo 4 visitas al mes)
        $visitasInvitado = Guest::where('cedula', $data['cedula'])
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $year)
            ->count();

        if ($visitasInvitado >= 4) {
            throw new Exception('Este invitado ya ha ingresado el máximo de 4 veces permitidas en este mes.', 422);
        }

        // 3. Flujo de Sincronización y Creación (Atómico)
        return DB::transaction(function () use ($data, $fecha) {

            // A. Sincronizar con el catálogo (0cc_invitados)
            // Ahora la unicidad es la combinación de Cédula + Acción (Socio)
            RegisteredGuest::updateOrCreate(
                [
                    'cedula' => $data['cedula'],
                    'acc'    => $data['acc']      //Buscamos por ambos campos
                ],
                [
                    'nombre'    => $data['nombre'],
                    'last_time' => now()->timestamp,
                    'operador'  => $data['operador'] ?? null
                ]
            );

            // B. Crear el registro en el historial de invitados
            return Guest::create($data);
        });
    }

    /**
     * Lista los invitados de un socio, paginados por año (1 año = 1 página),
     * y agrupados internamente por mes.
     */
    public function getGuestsPaginatedByYear(int $acc): LengthAwarePaginator
    {
        // 1. Obtener los años distintos en los que el socio tiene invitados.
        // Paginamos de 1 en 1 para que cada página represente un año exacto.
        $paginatedYears = Guest::where('acc', $acc)
            ->selectRaw('YEAR(fecha) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->paginate(1);

        // Si el socio no tiene registros, retornamos la paginación vacía
        if ($paginatedYears->isEmpty()) {
            return $paginatedYears;
        }

        $currentYear = $paginatedYears->first()->year;

        // 2. Extraer todos los invitados del socio para ese año específico
        $guests = Guest::where('acc', $acc)
            ->whereYear('fecha', $currentYear)
            ->orderBy('fecha', 'desc')
            ->get();

        // 3. Agrupar los resultados por mes (formato 'm' devuelve '01', '02', etc.)
        $groupedByMonth = $guests->groupBy(function ($guest) {
            return Carbon::parse($guest->fecha)->format('m');
        });

        // 4. Transformar la colección de la página actual para que devuelva
        // la estructura anidada exacta que requieres.
        $paginatedYears->setCollection(collect([
            [
                'anio' => $currentYear,
                'meses' => $groupedByMonth
            ]
        ]));

        return $paginatedYears;
    }

    /**
     * Actualiza un invitado existente validando reglas de negocio.
     * @throws Exception
     */
    public function updateGuest(int $ind, array $data): Guest
    {
        $guest = Guest::findOrFail($ind);

        $fecha = Carbon::parse($data['fecha'] ?? $guest->fecha);
        $mes = $fecha->month;
        $year = $fecha->year;
        $acc = $data['acc'] ?? $guest->acc;
        $cedula = $data['cedula'] ?? $guest->cedula;

        // 1. Validar límite del socio (Excluyendo el registro actual si es el mismo mes/año)
        $invitacionesSocio = Guest::where('acc', $acc)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $year)
            ->where('ind', '!=', $ind) // Importante: excluirse a sí mismo
            ->count();

        if ($invitacionesSocio >= 24) {
            throw new Exception('El socio titular ya alcanzó el límite de 24 invitaciones para ese periodo.', 422);
        }

        // 2. Validar límite del invitado (Excluyendo el registro actual)
        $visitasInvitado = Guest::where('cedula', $cedula)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $year)
            ->where('ind', '!=', $ind)
            ->count();

        if ($visitasInvitado >= 4) {
            throw new Exception('El invitado ya tiene el máximo de 4 ingresos permitidos en ese periodo.', 422);
        }

        $guest->update($data);
        return $guest;
    }

    /**
     * Elimina un registro de invitado.
     */
    public function deleteGuest(int $ind): bool
    {
        $guest = Guest::findOrFail($ind);
        return $guest->delete();
    }

    /**
     * Obtiene los invitados de una acción para el mes y año en curso.
     */
    public function getCurrentMonthGuests(int $acc): Collection
    {
        return Guest::where('acc', $acc)
            ->currentMonth() // Uso del scope definido en tu modelo
            ->orderBy('fecha', 'desc')
            ->get();
    }

}
