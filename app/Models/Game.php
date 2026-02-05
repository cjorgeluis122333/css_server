<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'domino_2025_partidos';
    const UPDATED_AT = 'fecha_actualizacion';
    const CREATED_AT = null;

    protected $fillable = [
        'ronda_id', 'pareja1_id', 'pareja2_id', 'puntos1', 'puntos2', 'terminado_tiempo'
    ];

    public function round()
    {
        return $this->belongsTo(Round::class, 'ronda_id');
    }

    public function couple1()
    {
        return $this->belongsTo(Couple::class, 'pareja1_id');
    }

    public function couple2()
    {
        return $this->belongsTo(Couple::class, 'pareja2_id');
    }
}
