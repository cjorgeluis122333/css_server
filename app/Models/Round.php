<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{

    protected $table = 'domino_2025_rondas';
    public $timestamps = false;

    protected $fillable = [
        'torneo_id', 'numero'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class, 'torneo_id');
    }

    public function matches()
    {
        return $this->hasMany(Game::class, 'ronda_id');
    }
}
