<?php

namespace App\Models\activities;

use Illuminate\Database\Eloquent\Model;

class OnboxPago extends Model
{
    protected $table = '0cc_onbox_pagos_all';

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
        'monto'   => 'decimal:2',
        'dolares' => 'decimal:2',
        'zelle'   => 'decimal:2',
        'recibo'  => 'integer',
        'fecha'   => 'integer',
    ];
}
