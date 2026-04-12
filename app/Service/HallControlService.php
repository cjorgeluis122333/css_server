<?php

namespace App\Service;

use App\Models\HallControl;
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
        // 1. Obtener el registro con la fecha más reciente
        $latestRecord = HallControl::orderBy('fecha', 'desc')->first();

        // Si la tabla está vacía, retornamos una colección vacía para no romper la vista
        if (!$latestRecord) {
            return collect([]);
        }

        // 2. Calcular exactamente 30 días hacia atrás desde esa última fecha
        $dateLimit = \Carbon\Carbon::parse($latestRecord->fecha)
            ->subDays(30)
            ->toDateString();

        // 3. Traer TODOS los salones desde la fecha límite calculada, sin usar take()
        return HallControl::where('fecha', '>=', $dateLimit)
            ->orderBy('fecha', 'desc')
            ->orderBy('salon', 'asc') // Opcional: Agrupa alfabéticamente los salones del mismo día
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

    public function update(HallControl $registro, array $data): HallControl
    {
        $registro->update($data);
        return $registro;
    }

    public function delete(HallControl $registro): bool
    {
        return $registro->delete();
    }
}
