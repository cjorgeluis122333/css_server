<?php

namespace App\Service;
use App\Models\Guest;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Support\Collection;
class GuestService
{
    /**
     * Inserta un nuevo invitado validando las reglas de negocio en la fecha dada.
     *
     * @throws Exception Si se superan los límites permitidos.
     */
    public function createGuest(array $data): Guest
    {
        $fecha = Carbon::parse($data['fecha']);
        $mes = $fecha->month;
        $year = $fecha->year;

        // 1. Validar límite del socio (Máximo 12 al mes)
        $invitacionesSocio = Guest::where('acc', $data['acc'])
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $year)
            ->count();

        if ($invitacionesSocio >= 12) {
            throw new Exception('El socio titular ya ha alcanzado el límite de 12 invitaciones para este mes.', 422);
        }

        // 2. Validar límite del invitado (Máximo 4 visitas al mes)
        $visitasInvitado = Guest::where('cedula', $data['cedula'])
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $year)
            ->count();

        if ($visitasInvitado >= 4) {
            throw new Exception('Este invitado ya ha ingresado el máximo de 4 veces permitidas en este mes.', 422);
        }

        // 3. Crear el registro si pasa las validaciones
        return Guest::create($data);
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
}
