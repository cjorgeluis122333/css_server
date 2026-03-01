<?php

namespace App\Service;

use App\Models\ManagerBoards;

class BoardsService
{
    /**
     * Crea o actualiza una junta directiva por año
     */
    public function saveBoard(array $data): ManagerBoards
    {
        // updateOrCreate busca por 'year', si existe actualiza, si no, crea.
        return ManagerBoards::updateOrCreate(
            ['year' => $data['year']],
            $data
        );
    }

    public function getBoardWithNames(int $year)
    {
        // Cargamos la junta con sus relaciones para traer los nombres de los directivos
        return ManagerBoards::with([
            'rel_presidente', 'rel_vicepresidente', 'rel_tesorero'
            // Añade aquí las relaciones que quieras incluir en la respuesta
        ])->find($year);
    }
}
