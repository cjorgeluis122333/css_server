<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = '0cc_directivos_datos';
    protected $primaryKey = 'ind';
    public $timestamps = false;

    protected $fillable = [
        'acc', 'cedula', 'nombre'
    ];

}
