<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Substitution extends Model
{

    protected $table = 'domino_2025_sustituciones';
    public $timestamps = false;

    protected $fillable = [
        'torneo_id', 'pareja_id', 'jugador_saliente', 'jugador_entrante', 'ronda', 'activa'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class, 'torneo_id');
    }

    public function couple()
    {
        return $this->belongsTo(Couple::class, 'pareja_id');
    }
}
