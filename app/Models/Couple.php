<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Couple extends Model
{
    protected $table = 'domino_2025_parejas';
    public $timestamps = false;

    protected $fillable = [
        'torneo_id', 'equipo', 'jugador1', 'jugador2', 'activa'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class, 'torneo_id');
    }

    public function matchesAsPair1()
    {
        return $this->hasMany(Game::class, 'pareja1_id');
    }

    public function matchesAsPair2()
    {
        return $this->hasMany(Game::class, 'pareja2_id');
    }

    public function substitutions()
    {
        return $this->hasMany(Substitution::class, 'pareja_id');
    }
}
