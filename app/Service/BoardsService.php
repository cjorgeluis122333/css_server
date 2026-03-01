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
        return ManagerBoards::create($data);
    }

}
