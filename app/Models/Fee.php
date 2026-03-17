<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Fee extends Model
{
    protected $table = '0cc_cuotas';
    // Definimos la llave primaria personalizada
    protected $primaryKey = 'ind';
    public $timestamps = false;

    protected $fillable = [
        'mes',
        'cuota',
        'impuesto',
    ];

    /**
     * Casts de tipos.
     * Aunque 'mes' es un string, los decimales deben tratarse como float/double.
     */
    protected $casts = [
        'cuota' => 'decimal:2',
        'impuesto' => 'decimal:2',
    ];


    /**
     * Atributo virtual para obtener el total (Cuota + Impuesto).
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn() => (float)($this->cuota/* + $this->impuesto*/),
        );
    }
}
