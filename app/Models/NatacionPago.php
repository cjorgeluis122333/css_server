<?php

namespace App\Models\activities;

use Illuminate\Database\Eloquent\Model;
class NatacionPago extends Model
{

    // Nombre explícito de la tabla
    protected $table = '0cc_natacion_pagos';

    // Clave primaria personalizada
    protected $primaryKey = 'ind';

    // Indicar si la PK es autoincrementable
    public $incrementing = true;

    // Desactivar timestamps automáticos (created_at / updated_at) ya que la tabla no los tiene
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cedula',
        'anio',
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

    /**
     * Mutadores y Casts para asegurar los tipos de datos al interactuar con el modelo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ind' => 'integer',
        'cedula' => 'integer',
        'anio' => 'integer',
        'monto' => 'integer',
        'dolares' => 'integer',
        'zelle' => 'integer',
        'recibo' => 'integer',
        'fecha' => 'integer',
    ];
}
