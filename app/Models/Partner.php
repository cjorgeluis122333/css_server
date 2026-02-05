<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{

    protected $table = '0cc_socios';

    // Definimos la clave primaria personalizada
    protected $primaryKey = 'ind';

    // Si tu SQL original no tiene timestamps (created_at/updated_at),
    // cambia esto a false o asegúrate de añadirlos en la migración.
    public $timestamps = false;

    protected $fillable = [
        'sincro', 'acc', 'cedula', 'carnet', 'nombre', 'celular',
        'telefono', 'correo', 'direccion', 'nacimiento',
        'ingreso', 'ocupacion', 'categoria', 'cobrador'
    ];

    // Casting automático a objetos Carbon (Fecha)
    protected $casts = [
        'nacimiento' => 'date:Y-m-d',
        'ingreso' => 'date:Y-m-d',
        'sincro' => 'integer',
        'acc' => 'integer',
        'cobrador' => 'integer',
    ];
}
