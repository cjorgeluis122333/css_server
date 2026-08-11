<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class BattingPago extends Model
{
    protected $table = '0cc_batting_pagos_unificada';

    protected $primaryKey = 'ind';

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
        'ind'     => 'integer',
        'cedula'  => 'integer',
        'monto'   => 'integer',
        'dolares' => 'integer',
        'zelle'   => 'integer',
        'recibo'  => 'integer',
        'fecha'   => 'integer',
    ];
}
