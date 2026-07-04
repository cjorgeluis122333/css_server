<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class StrongPago extends Model
{
    protected $table = '0cc_strong_pagos_unificada';

    protected $primaryKey = 'id_global';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'ind_original',
        'ano',
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
        'id_global'    => 'integer',
        'ind_original' => 'integer',
        'ano'          => 'integer',
        'cedula'       => 'integer',
        'monto'        => 'integer',
        'dolares'      => 'integer',
        'zelle'        => 'integer',
        'recibo'       => 'integer',
        'fecha'        => 'integer',
    ];
}
