<?php

namespace App\Service;

use App\Models\RegisteredGuest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class RegisteredGuestService
{
    /**
     * Obtiene una lista paginada de todos los invitados registrados.
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return RegisteredGuest::orderBy('nombre', 'asc')->paginate($perPage);
    }

    /**
     * Busca un invitado específicamente por su cédula.
     * Ideal para validar antes de hacer un insert en el historial.
     */
    public function findByCedula(string $cedula): ?RegisteredGuest
    {
        return RegisteredGuest::byCedula($cedula)->first();
    }

    /**
     * Búsqueda rápida por nombre para el autocompletado del frontend.
     * Utiliza el scope definido en el modelo. Limitamos a 20 resultados para rendimiento.
     */
    public function searchForAutocomplete(string $nombre): Collection
    {
        return RegisteredGuest::searchByName($nombre)
            ->limit(20)
            ->get(['ind', 'cedula', 'nombre', 'acc']); // Retornamos solo lo necesario para el select
    }

    /**
     * Obtiene los invitados que han sido registrados previamente por un socio en específico.
     */
    public function getBySocio(int $acc): Collection
    {
        return RegisteredGuest::where('acc', $acc)
            ->orderBy('last_time', 'desc')
            ->get();
    }

    /**
     * Lógica principal para cuando un invitado ingresa al club.
     * Si la cédula existe, actualiza su 'last_time' y los datos recientes.
     * Si no existe, lo crea.
     */
    public function registerOrUpdateGuest(array $data): RegisteredGuest
    {
        // updateOrCreate busca por el primer array (cedula) y actualiza/crea con el segundo
        return RegisteredGuest::updateOrCreate(
            ['cedula' => $data['cedula']],
            [
                'nombre' => $data['nombre'],
                'acc' => $data['acc'] ?? null,
                'last_time' => $data['last_time'] ?? now(),
                'operador' => $data['operador'] ?? null
            ]
        );
    }

    /**
     * Crea un invitado manualmente desde un CRUD administrativo.
     */
    public function createGuest(array $data): RegisteredGuest
    {
        return RegisteredGuest::create($data);
    }

    /**
     * Actualiza los datos de un invitado existente en el catálogo.
     */
    public function updateGuest(int $ind, array $data): RegisteredGuest
    {
        $guest = RegisteredGuest::findOrFail($ind);
        $guest->update($data);

        return $guest;
    }

    /**
     * Elimina un registro de invitado del catálogo.
     */
    public function deleteGuest(int $ind): bool
    {
        $guest = RegisteredGuest::findOrFail($ind);
        return $guest->delete();
    }
}
