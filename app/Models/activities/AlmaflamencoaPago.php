<?php

namespace App\Models\activities;

use Illuminate\Database\Eloquent\Model;

class AlmaflamencoaPago extends Model
{
    protected $table = '0cc_almaflamenca_pagos_unificada';

    protected $primaryKey = 'id_pago';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'ind_original',
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
        'id_pago'      => 'integer',
        'ind_original' => 'integer',
        'cedula'       => 'integer',
        'monto'        => 'integer',
        'dolares'      => 'integer',
        'zelle'        => 'integer',
        'recibo'       => 'integer',
        'fecha'        => 'integer',
    ];
}
