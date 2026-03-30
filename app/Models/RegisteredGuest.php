<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enum\PartnerCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RegisteredGuest extends Model
{
    protected $table = '0cc_invitados';

    // Definimos la llave primaria personalizada
    protected $primaryKey = 'ind';

    // Desactivamos los timestamps si decidiste no incluirlos en la migración
    public $timestamps = false;

    protected $fillable = [
        'cedula',
        'nombre',
        'acc',
        'last_time',
        'operador'
    ];

    // Casteamos los tipos de datos para que Laravel los maneje correctamente
    protected $casts = [
        'cedula'    => 'string', // O 'integer' si lo definiste así en la migración
        'acc'       => 'integer',
        'last_time' => 'datetime', // Carbon se encargará de esto automáticamente
    ];

    // --- RELACIONES ---

    /**
     * Relación con el Socio que registró originalmente al invitado.
     */
    public function socioTitular(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'acc', 'acc')
            ->where('categoria', PartnerCategory::TITULAR);
    }

    // --- SCOPES DE BÚSQUEDA Y OPTIMIZACIÓN ---

    /**
     * Scope para buscar rápidamente por cédula.
     */
    public function scopeByCedula(Builder $query, string $cedula): void
    {
        $query->where('cedula', $cedula);
    }

    /**
     * Scope para el autocompletado en el frontend.
     * Busca coincidencias parciales usando el índice B-Tree (solo funciona eficientemente si el comodín va al final).
     */
    public function scopeSearchByName(Builder $query, string $nombre): void
    {
        // Al usar 'Termino%' MySQL puede usar el índice. '%Termino%' anula el uso del índice.
        $query->where('nombre', 'LIKE', $nombre . '%');
    }

}
