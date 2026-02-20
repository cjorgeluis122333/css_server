<?php

namespace App\Models;

use App\Enum\PartnerCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    // --- ACCESSORS ---

    /**
     * Calculate age based on birthdate.
     * Usage: $partner->age
     */
    public function getAgeAttribute(): ?int
    {
        return $this->nacimiento?->age;
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
