<?php

namespace App\Service;
use App\Enum\PartnerCategory;
use Illuminate\Support\Facades\DB;
use App\Models\Partner;
class PartnerService
{
    /**
     * Crea un socio de tipo Titular con sus valores por defecto.
     */
    public function createTitular(array $data): Partner
    {
        return DB::transaction(function () use ($data) {
            return Partner::create(array_merge($data, [
                'categoria' => PartnerCategory::TITULAR,
                'sincro' => 0, // Valor por defecto para sincronización
            ]));
        });
    }

    /**
     * Actualiza un socio Titular existente.
     */
    public function updateTitular(Partner $partner, array $data): Partner
    {
        return DB::transaction(function () use ($partner, $data) {
            $partner->fill($data);

            // Si el modelo ha cambiado, forzamos la resincronización
            if ($partner->isDirty()) {
                $partner->sincro = 0;
            }

            $partner->save();
            return $partner;
        });
    }
    /**
     * Elimina un socio validando reglas de negocio.
     * @throws \Exception
     */
    public function deleteTitular(Partner $partner): void
    {
        // Regla de Negocio: No borrar si tiene familiares
        if ($partner->dependents()->exists()) {
            throw new \Exception('No se puede eliminar: El socio tiene familiares asociados.');
        }

        $partner->delete();
    }

//    =========================================== Familiar
    /**
     * Crea un socio de tipo Familiar.
     */
    public function createFamiliar(array $data): Partner
    {
        return DB::transaction(function () use ($data) {
            $data['categoria'] = PartnerCategory::FAMILIAR;
            $data['sincro'] = 0;
            $data['cobrador'] = 0; // Familiares no suelen tener cobrador

            return Partner::create($data);
        });
    }

    /**
     * Actualiza un socio de tipo Familiar.
     */
    public function updateFamiliar(Partner $familiar, array $data): Partner
    {
        return DB::transaction(function () use ($familiar, $data) {
            // Prevenir que cambien la cuenta (acc) accidentalmente al editar
            if (isset($data['acc'])) {
                unset($data['acc']);
            }

            $familiar->fill($data);

            if ($familiar->isDirty()) {
                $familiar->sincro = 0;
            }

            $familiar->save();
            return $familiar;
        });
    }

    /**
     * Elimina un socio de tipo Familiar.
     */
    public function deleteFamiliar(Partner $familiar): void
    {
        // Aquí no hay restricción de dependientes, se borra directo
        $familiar->delete();
    }


}
