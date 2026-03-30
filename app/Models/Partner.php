<?php

namespace App\Models;

use App\Enum\PartnerCategory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{

    protected $table = '0cc_socios';
    protected $primaryKey = 'ind';

    public $timestamps = false;

    protected $fillable = [
        'sincro', 'acc', 'cedula', 'carnet', 'nombre', 'celular',
        'telefono', 'correo', 'direccion', 'nacimiento',
        'ingreso', 'ocupacion', 'categoria', 'cobrador'
    ];

    // Casting automático a objetos Carbon (Fecha)
    protected $casts = [
//        'nacimiento' => 'date',
//        'ingreso' => 'date',
        'sincro' => 'integer',
        'acc' => 'integer',
        'cobrador' => 'integer',
        'categoria'  => PartnerCategory::class,
    ];


    // --- SCOPES ---

    /**
     * Filter query to include only main account holders.
     * Usage: Partner::holders()->get();
     */
    public function scopeHolders(Builder $query): void
    {
        $query->where('categoria', PartnerCategory::TITULAR->value);
    }

    /**
     * Filter query to include only family dependents.
     * Usage: Partner::onlyDependents()->get();
     */
    public function scopeOnlyDependents(Builder $query): void
    {
        $query->where('categoria', PartnerCategory::FAMILIAR->value);
    }

    // --- RELATIONS ---

    /**
     * Get all family members associated with the same account (acc).
     */
    public function dependents(): HasMany
    {
        return $this->hasMany(Partner::class, 'acc', 'acc')
            ->where('categoria', PartnerCategory::FAMILIAR);
    }

    /**
     * Get the main holder of the account.
     */
    public function holder(): HasOne
    {
        return $this->hasOne(Partner::class, 'acc', 'acc')
            ->where('categoria', PartnerCategory::TITULAR);
    }

    /**
     * Get the invitations made by this account.
     * Only valid for the main holder.
     */
    public function invitations(): HasMany
    {
        // Solo el titular debería poder ver/gestionar las invitaciones asociadas a su 'acc'
        return $this->hasMany(Guest::class, 'acc', 'acc');
    }

    public function paymentHistories(): HasMany
    {
        // Relacionamos por la columna 'acc' que comparten ambas tablas
        return $this->hasMany(HistoryPay::class, 'acc', 'acc');
    }

    // --- ACCESSORS ---

    /**
     * Calculate age based on birthdate.
     * Usage: $partner->age
     */
    public function getAgeAttribute(): ?int
    {
        // 1. Filtramos valores vacíos o strings de ceros (0, 00, 000, etc)
        if (empty($this->nacimiento) || preg_match('/^0+$/', str_replace(['-', '/'], '', $this->nacimiento))) {
            return null;
        }

        try {
            $date = Carbon::parse($this->nacimiento);

            // 2. Filtramos fechas absurdas como 0001-01-01
            // Si la fecha es menor al año 1900, la consideramos inválida para el cálculo
            if ($date->year < 1900) {
                return null;
            }

            return $date->age;
        } catch (Exception $e) {
            // Si Carbon no puede parsear el string (ej: "datos_corruptos"), devolvemos null
            return null;
        }
    }


    public function getFechaIngresoValidadaAttribute()
    {
        $value = trim($this->ingreso);

        // Lista de valores que consideramos "vacíos" o "basura"
        $invalidValues = [null, '', '-'];

        if (in_array($value, $invalidValues, true)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m');
        } catch (\Exception $e) {
            return null; // Si el formato es extraño y falla el parseo
        }
    }

    // --- BUSINESS LOGIC ---

    /**
     * Check if the instance is a main account holder.
     */
    public function isHolder(): bool
    {
        return $this->categoria === PartnerCategory::TITULAR;
    }

    /**
     * Check if the instance is a dependent.
     */
    public function isDependent(): bool
    {
        return $this->categoria === PartnerCategory::FAMILIAR;
    }
}
