<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class PinponPago extends Model
{
    protected $table = '0cc_pinpon_pagos_unificada';

    // PK compuesta — Laravel no soporta PK compuesta nativamente
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'ind_original',
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
        'ind_original' => 'integer',
        'anio_origen'  => 'integer',
        'cedula'       => 'integer',
        'monto'        => 'integer',
        'dolares'      => 'integer',
        'zelle'        => 'integer',
        'recibo'       => 'integer',
        'fecha'        => 'integer',
    ];
}
