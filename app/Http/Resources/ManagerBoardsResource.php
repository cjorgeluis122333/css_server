<?php

namespace App\Http\Resources;

use App\Models\ManagerBoards;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ManagerBoards */
class ManagerBoardsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'year'           => $this->year,
            // Reemplazamos el string por el objeto de la relación
            'presidente'     => $this->rel_presidente,
            'vicepresidente' => $this->rel_vicepresidente,
            'secretario'     => $this->rel_secretario,
            'vicesecretario' => $this->rel_vicesecretario,
            'tesorero'       => $this->rel_tesorero,
            'vicetesorero'   => $this->rel_vicetesorero,
            'bibliotecario'  => $this->rel_bibliotecario,
            'actas'          => $this->rel_actas,
            'viceactas'      => $this->rel_viceactas,
            'actos'          => $this->rel_actos,
            'deportes'       => $this->rel_deportes,
            'vocal1'         => $this->rel_vocal1,
            'vocal2'         => $this->rel_vocal2,
        ];
    }
}
