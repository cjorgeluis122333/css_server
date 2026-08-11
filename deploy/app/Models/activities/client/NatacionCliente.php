<?php

namespace App\Models\activities\client;

use Illuminate\Database\Eloquent\Model;

class NatacionCliente extends Model
{
    protected $table = '0cc_natacion_clientes';

    protected $primaryKey = 'ind';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'cedula',
        'nombre',
        'nacimiento',
        'sexo',
        'socio',
        'padres',
        'repre_cedula1',
        'repre_nombre1',
        'repre_cedula2',
        'repre_nombre2',
        'repre_cedula3',
        'repre_nombre3',
        'last_pay',
        'last_pay_mont',
        'operador',
    ];

    protected $casts = [
        'ind'    => 'integer',
        'cedula' => 'integer',
    ];
}
