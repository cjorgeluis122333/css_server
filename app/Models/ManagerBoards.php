<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerBoards extends Model
{
    protected $table = '0cc_directivos_juntas';
    protected $primaryKey = 'year';
    public $incrementing = false; // El año no es autoincremental
    public $timestamps = false;

    protected $fillable = [
        'year', 'presidente', 'vicepresidente', 'secretario', 'vicesecretario',
        'tesorero', 'vicetesorero', 'bibliotecario', 'actas', 'viceactas',
        'actos', 'deportes', 'vocal1', 'vocal2'
    ];

    /**
     * Definimos una relación genérica para no repetir código
     */
    private function managerBy(string $column): BelongsTo
    {
        // Relacionamos la columna de la junta con la 'cedula' del Manager
        return $this->belongsTo(Manager::class, $column, 'cedula');
    }

    // Relaciones específicas
    public function rel_presidente() { return $this->managerBy('presidente'); }
    public function rel_vicepresidente() { return $this->managerBy('vicepresidente'); }
    public function rel_tesorero() { return $this->managerBy('tesorero'); }
    // ... Puedes añadir las demás según necesites cargar los nombres
}
