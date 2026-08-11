<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class BasquetPago extends Model
{
    protected $table = '0cc_basquet_pagos';

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
