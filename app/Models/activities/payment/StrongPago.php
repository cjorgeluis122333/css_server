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

    protected $hidden = ['ano'];

    protected $casts = [
        'id_global' => 'integer',
        'ano' => 'integer',
        'cedula' => 'string',
        'monto' => 'integer',
        'dolares' => 'integer',
        'zelle' => 'integer',
        'recibo' => 'string',
        'fecha' => 'integer',
    ];
}
