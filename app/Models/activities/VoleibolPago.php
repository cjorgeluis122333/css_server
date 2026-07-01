<?php

namespace App\Models\activities;

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

    protected $casts = [
        'ind'        => 'integer',
        'cedula'     => 'integer',
        'monto'      => 'integer',
        'dolares'    => 'integer',
        'zelle'      => 'integer',
        'recibo'     => 'integer',
        'fecha'      => 'integer',
        'ano_origen' => 'integer',
    ];
}
