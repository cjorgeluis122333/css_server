<?php

namespace App\Http\Resources\activity\client;

use App\Models\activities\client\InglesCliente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InglesCliente */
class InglesClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->ind,
            'cedula'     => $this->cedula,
            'nombre'     => $this->nombre,
            'socio'      => $this->socio,
            'nacimiento' => $this->nacimiento,
            'sexo'       => $this->sexo,
        ];
    }
}
