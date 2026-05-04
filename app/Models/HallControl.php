<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallControl extends Model
{
    // Especificar la tabla exacta
    protected $table = '0cc_salones_control_unificado';

    // Especificar la llave primaria personalizada
    protected $primaryKey = 'ind';

    // Desactivar timestamps ya que no están en la migración
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'fecha',
        'salon',
        'acc',
        'nombre',
        'abono',
        'pago',
        'pases',
        'hora',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
