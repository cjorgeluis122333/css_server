<?php

namespace App\Http\Resources\activity\client;

use App\Models\activities\client\BasquetCliente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BasquetCliente */
class BasquetClienteResource extends JsonResource
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
