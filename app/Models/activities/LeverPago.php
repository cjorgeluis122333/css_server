<?php

namespace App\Models\activities;

use Illuminate\Database\Eloquent\Model;

class LeverPago extends Model
{
    protected $table = '0cc_lever_pagos_unificado';

    protected $primaryKey = 'id_pago';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
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

    protected $casts = [
        'id_pago' => 'integer',
        'cedula'  => 'integer',
        'monto'   => 'decimal:2',
        'dolares' => 'decimal:2',
        'zelle'   => 'decimal:2',
        'recibo'  => 'integer',
        'fecha'   => 'integer',
    ];
}
