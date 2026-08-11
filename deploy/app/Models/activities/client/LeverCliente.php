<?php

namespace App\Models\activities\client;

use Illuminate\Database\Eloquent\Model;

class LeverCliente extends Model
{
    protected $table = '0cc_lever_clientes';

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
        'last_pay',
        'last_pay_mont',
        'd',
        'operador',
    ];

    protected $casts = [
        'ind'    => 'integer',
        'cedula' => 'integer',
    ];
}
