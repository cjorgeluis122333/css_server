<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HallControlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['ejecutado_por'] = $this->when(
            $request->user() && $request->user()->isSuperAdmin(),
            fn () => $this->creator?->correo
        );

        return $data;
    }
}
