<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $table = 'domino_2025_torneos';
    public $timestamps = false; // La tabla usa fecha_creacion manualmente

    protected $fillable = [
        'nombre', 'sede', 'fecha', 'equipos_participantes', 'finalizado'
    ];

    protected $casts = [
        'equipos_participantes' => 'array',
        'finalizado' => 'boolean',
        'fecha' => 'date'
    ];

    public function rounds()
    {
        return $this->hasMany(Round::class, 'torneo_id');
    }

    public function couple()
    {
        return $this->hasMany(Couple::class, 'torneo_id');
    }

    public function substitutions()
    {
        return $this->hasMany(Substitution::class, 'torneo_id');
    }
}
