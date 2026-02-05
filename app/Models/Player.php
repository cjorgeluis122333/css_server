<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'domino_jugadores';
    public $timestamps = false;

    protected $fillable = [
        'cedula', 'nombre_completo', 'equipo_abreviatura'
    ];

    public function equipo()
    {
        return $this->belongsTo(Team::class, 'equipo_abreviatura', 'abreviatura');
    }
}
