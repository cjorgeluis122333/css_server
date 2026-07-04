<?php

namespace App\Models\activities\client;

use Illuminate\Database\Eloquent\Model;

class StrongCliente extends Model
{
    protected $table = '0cc_strong_clientes';

    protected $primaryKey = 'cedula';

    public $incrementing = false;

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
        'cedula' => 'integer',
    ];
}
