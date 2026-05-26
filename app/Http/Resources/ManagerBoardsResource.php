<?php

namespace App\Http\Resources;

use App\Models\ManagerBoards;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ManagerBoards */
class ManagerBoardsResource extends JsonResource
{
    private function managerResource(?object $manager): ?ManagerResource
    {
        return $manager ? new ManagerResource($manager) : null;
    }

    public function toArray(Request $request): array
    {
        return [
            'year' => $this->year,
            'presidente' => $this->managerResource($this->rel_presidente),
            'vicepresidente' => $this->managerResource($this->rel_vicepresidente),
            'secretario' => $this->managerResource($this->rel_secretario),
            'vicesecretario' => $this->managerResource($this->rel_vicesecretario),
            'tesorero' => $this->managerResource($this->rel_tesorero),
            'vicetesorero' => $this->managerResource($this->rel_vicetesorero),
            'bibliotecario' => $this->managerResource($this->rel_bibliotecario),
            'actas' => $this->managerResource($this->rel_actas),
            'viceactas' => $this->managerResource($this->rel_viceactas),
            'actos' => $this->managerResource($this->rel_actos),
            'deportes' => $this->managerResource($this->rel_deportes),
            'vocal1' => $this->managerResource($this->rel_vocal1),
            'vocal2' => $this->managerResource($this->rel_vocal2),
        ];
    }
}
