<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class HistoryPay extends Model
{
    protected $table = 'historial_pagos_unificado';
    protected $primaryKey = 'ind';
    public $timestamps = false; // Tu SQL no tiene created_at/updated_at

    protected $fillable = [
        'acc', 'time', 'fecha', 'mes', 'oper',
        'monto', 'descript', 'seniat', 'operador'
    ];

    /**
     * Relación inversa: Un historial pertenece a un Socio (Partner)
     * Se usa 'acc' como llave foránea y local.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'acc', 'acc');
    }
}
