<?php

namespace App\Service;

use App\Models\ManagerBoards;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ManagerBoardsService
{
    private const RELATIONS = [
        'rel_presidente',
        'rel_vicepresidente',
        'rel_secretario',
        'rel_vicesecretario',
        'rel_tesorero',
        'rel_vicetesorero',
        'rel_bibliotecario',
        'rel_actas',
        'rel_viceactas',
        'rel_actos',
        'rel_deportes',
        'rel_vocal1',
        'rel_vocal2',
    ];

    public function getAll(): Collection
    {
        return ManagerBoards::with(self::RELATIONS)->get();
    }

    public function getByYear(int $year): ManagerBoards|array
    {
        $board = ManagerBoards::with(self::RELATIONS)->find($year);

        if (! $board) {
            return [
                'year' => $year,
                'presidente' => null,
                'vicepresidente' => null,
                'secretario' => null,
                'vicesecretario' => null,
                'tesorero' => null,
                'vicetesorero' => null,
                'bibliotecario' => null,
                'actas' => null,
                'viceactas' => null,
                'actos' => null,
                'deportes' => null,
                'vocal1' => null,
                'vocal2' => null,
            ];
        }

        return $board;
    }

    public function upsertBoard(array $data): ManagerBoards
    {
        return DB::transaction(function () use ($data) {
            $board = ManagerBoards::updateOrCreate(
                ['year' => $data['year']],
                $data
            );

            return $board->load(self::RELATIONS);
        });
    }

    public function delete(int $year): bool
    {
        return DB::transaction(function () use ($year) {
            $board = ManagerBoards::findOrFail($year);

            return $board->delete();
        });
    }
}
