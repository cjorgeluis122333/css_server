<?php

namespace App\Models\activities\payment;

use Illuminate\Database\Eloquent\Model;

class VoleibolPago extends Model
{
    protected $table = '0cc_voleibol_pagos_unificado';

    protected $primaryKey = 'ind';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
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
        'ano_origen',
    ];

    protected $hidden = ['ano_origen'];

    protected $casts = [
        'ind' => 'integer',
        'cedula' => 'string',
        'monto' => 'integer',
        'dolares' => 'integer',
        'zelle' => 'integer',
        'recibo' => 'string',
        'fecha' => 'integer',
        'ano_origen' => 'integer',
    ];
}
