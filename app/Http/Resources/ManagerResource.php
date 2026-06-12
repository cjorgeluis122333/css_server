<?php

namespace App\Http\Resources;

use App\Models\partners\Manager;
use App\Service\photo\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Manager */
class ManagerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ind' => $this->ind,
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'acc' => $this->acc,
            'url' => app(PhotoService::class)->getUrl($this->cedula),
        ];
    }
}
