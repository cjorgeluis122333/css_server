<?php

namespace App\Models\activities;

use Illuminate\Database\Eloquent\Model;

class KaratePago extends Model
{
    protected $table = '0cc_karate_pagos';

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
