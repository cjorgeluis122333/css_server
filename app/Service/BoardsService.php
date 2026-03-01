<?php

namespace App\Service;

use App\Models\ManagerBoards;

class BoardsService
{
    public function findByYear(int $year): ?ManagerBoards
    {
        return ManagerBoards::find($year);
    }

    public function saveBoard(array $data): ManagerBoards
    {
        return ManagerBoards::create($data);
    }

    public function updateBoard(ManagerBoards $board, array $data): ManagerBoards
    {
        $board->update($data);
        return $board;
    }

}
