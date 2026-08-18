<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class InglesPago extends Model
{
    protected $table = '0cc_ingles_pagos_unificado';

    // PK compuesta — Laravel no soporta PK compuesta nativamente
    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ano_tabla',
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

    protected $hidden = ['ano_tabla'];

    protected $casts = [
        'id'       => 'integer',
        'ano_tabla' => 'integer',
        'cedula'    => 'integer',
        'monto'     => 'integer',
        'dolares'   => 'integer',
        'zelle'     => 'integer',
        'recibo'    => 'integer',
        'fecha'     => 'integer',
    ];
}
