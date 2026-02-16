<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
class Partner extends Model
{

    protected $table = '0cc_socios';

    // Definimos la clave primaria personalizada
    protected $primaryKey = 'ind';

    // Si tu SQL original no tiene timestamps (created_at/updated_at),
    // cambia esto a false o asegúrate de añadirlos en la migración.
    public $timestamps = true;

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


    /**
     * Scope Global: Por defecto, este modelo SOLO traerá Titulares.
     */
    protected static function booted()
    {
        static::addGlobalScope('solo_titulares', function (Builder $builder) {
            $builder->where('categoria', 'titular');
        });
    }

    /**
     * Accessor para la Edad: $partner->edad
     */
    public function getEdadAttribute()
    {
        return $this->nacimiento ? Carbon::parse($this->nacimiento)->age : null;
    }

    /**
     * Relación: Si un titular tiene familiares en la misma tabla
     */
    public function familiares()
    {
        // Relación: misma tabla, mismo 'acc', pero categoría 'familiar'
        return $this->hasMany(Partner::class, 'acc', 'acc')
            ->withoutGlobalScope('solo_titulares') // Importante: ignorar el filtro global
            ->where('categoria', 'familiar');
    }
}
