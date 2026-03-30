<?php

namespace App\Models;
use App\Enum\PartnerCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
class Guest extends Model
{
    protected $table = '0cc_invitados_unificados';
    protected $primaryKey = 'ind';

    // Desactivamos los timestamps si la tabla original no tiene created_at / updated_at
    public $timestamps = false;

    protected $fillable = [
        'cedula', 'nombre', 'fecha', 'acc', 'fuente', 'operador'
    ];

    protected $casts = [
        'cedula' => 'integer',
        'acc'    => 'integer',
        'fecha'  => 'date',
    ];

    // --- RELATIONS ---

    /**
     * Relación con el Socio Titular de la acción.
     * Ignoramos a los familiares gracias a la cláusula where.
     */
    public function titular(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'acc', 'acc')
            ->where('categoria', PartnerCategory::TITULAR);
    }

    // --- SCOPES ---

    /**
     * Filtra los registros para obtener solo los del mes y año actuales.
     * Fundamental para validar las reglas de negocio (12 por socio / 4 por invitado).
     */
    public function scopeCurrentMonth(Builder $query): void
    {
        $now = Carbon::now();
        $query->whereMonth('fecha', $now->month)
            ->whereYear('fecha', $now->year);
    }
}
