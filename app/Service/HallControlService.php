<?php

namespace App\Service;

use App\Models\HallControl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class HallControlService
{
    public function getAll(): Collection
    {
        // Puedes cambiar esto por paginación si la tabla crece mucho
        return HallControl::all();
    }
    /**
     * Obtiene el historial de los salones de los últimos 30 días,
     * ordenado del más reciente al más antiguo, limitando a 30 registros.
     */
    public function getRecentHistory(): \Illuminate\Support\Collection
    {
        // 1. Definimos el intervalo: desde hoy hasta 30 días en el futuro
        $fechaInicio = Carbon::now()->startOfDay();
        $fechaFin = Carbon::now()->addDays(30)->endOfDay();

        // 2. Filtramos por el rango de fechas
        // Usamos whereBetween para que el código sea más legible y eficiente
        return HallControl::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc') // De hoy hacia el futuro
            ->orderBy('salon', 'asc') // Organización alfabética para el mismo día
            ->get();
    }
    public function getById(int $id): ?HallControl
    {
        return HallControl::find($id);
    }

    public function create(array $data): HallControl
    {
        return HallControl::create($data);
    }

   public function update(HallControl $salon, array $data): HallControl
    {
        // Eloquent actualizará solo los campos que vengan en $data
        $salon->update($data);
        return $salon;
    }

    /**
     * "Elimina" la reserva/ocupación devolviendo el salón a su estado inicial (Disponible)
     */
    public function resetToAvailable(HallControl $salon): HallControl
    {
        $salon->update([
            'acc'    => 0,
            'nombre' => null,
            'abono'  => null,
            'pago'   => null,
            'pases'  => null,
            'hora'   => null
        ]);

        return $salon;
    }
}
