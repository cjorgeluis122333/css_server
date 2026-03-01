<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(Manager::class, $column, 'cedula');
    }

    // Todas las relaciones específicas
    public function rel_presidente() { return $this->managerBy('presidente'); }
    public function rel_vicepresidente() { return $this->managerBy('vicepresidente'); }
    public function rel_secretario() { return $this->managerBy('secretario'); }
    public function rel_vicesecretario() { return $this->managerBy('vicesecretario'); }
    public function rel_tesorero() { return $this->managerBy('tesorero'); }
    public function rel_vicetesorero() { return $this->managerBy('vicetesorero'); }
    public function rel_bibliotecario() { return $this->managerBy('bibliotecario'); }
    public function rel_actas() { return $this->managerBy('actas'); }
    public function rel_viceactas() { return $this->managerBy('viceactas'); }
    public function rel_actos() { return $this->managerBy('actos'); }
    public function rel_deportes() { return $this->managerBy('deportes'); }
    public function rel_vocal1() { return $this->managerBy('vocal1'); }
    public function rel_vocal2() { return $this->managerBy('vocal2'); }

}
