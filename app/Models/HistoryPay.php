<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class HistoryPay extends Model
{
    protected $table = 'historial_pagos_separado';
    protected $primaryKey = 'ind';
    public $timestamps = false; // Tu SQL no tiene created_at/updated_at

    protected $fillable = [
        'acc', 'time', 'fecha', 'mes',
        'oper', 'resibo','control','factura',
        'monto', 'descript',
        'observaciones',
        'seniat', 'operador',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'acc', 'acc');
    }

}

