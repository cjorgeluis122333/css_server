<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class PinponPago extends Model
{
    protected $table = '0cc_pinpon_pagos_unificada';

    // PK compuesta — Laravel no soporta PK compuesta nativamente
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'anio_origen',
        'cedula',
        'mes',
        'd',
        'plan',
        'monto',
        'dolares',
        'zelle',
        'recibo',
        'fecha',
        'observacion',
        'operador',
    ];

    protected $hidden = ['anio_origen'];

    protected $casts = [
        'id' => 'integer',
        'anio_origen' => 'integer',
        'cedula' => 'string',
        'monto' => 'integer',
        'dolares' => 'integer',
        'zelle' => 'integer',
        'recibo' => 'string',
        'fecha' => 'integer',
    ];
}
