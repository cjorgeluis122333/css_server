<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class NatacionPago extends Model
{
    protected $table = '0cc_natacion_pagos';

    protected $primaryKey = 'ind';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'cedula',
        'anio',
        'mes',
        'plan',
        'monto',
        'dolares',
        'zelle',
        'recibo',
        'fecha',
        'observacion',
        'operador',
    ];

    protected $hidden = ['anio'];

    protected $casts = [
        'ind'     => 'integer',
        'cedula'  => 'integer',
        'anio'    => 'integer',
        'monto'   => 'integer',
        'dolares' => 'integer',
        'zelle'   => 'integer',
        'recibo'  => 'integer',
        'fecha'   => 'integer',
    ];
}
