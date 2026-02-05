<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'domino_equipos';
    public $timestamps = false;

    protected $fillable = [
        'nombre_completo', 'abreviatura'
    ];

    public function players()
    {
        return $this->hasMany(Player::class, 'equipo_abreviatura', 'abreviatura');
    }
}
