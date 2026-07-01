<?php

namespace App\Models\activities;

use Illuminate\Database\Eloquent\Model;

class InglesPago extends Model
{
    protected $table = '0cc_ingles_pagos_unificado';

    // PK compuesta — Laravel no soporta PK compuesta nativamente
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'ano_tabla',
        'ind',
        'cedula',
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

    protected $casts = [
        'ano_tabla' => 'integer',
        'ind'       => 'integer',
        'cedula'    => 'integer',
        'monto'     => 'integer',
        'dolares'   => 'integer',
        'zelle'     => 'integer',
        'recibo'    => 'integer',
        'fecha'     => 'integer',
    ];
}
