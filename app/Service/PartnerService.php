<?php

namespace App\Service;
use App\Enum\PartnerCategory;
use Illuminate\Support\Facades\DB;
use App\Models\Partner;
class PartnerService
{


    /**
     * Obtiene la lista de socios y familiares habilitados para el control de acceso.
     * Excluye cuentas en Tesorería, Desocupados y sus familiares asociados.
     */
    public function getValidPartnersForAccess()
    {
        return Partner::query()
            // 1. Cargamos los familiares (dependents) de cada titular
            ->with('dependents')
            // 2. Filtramos solo los Titulares (para que sea la raíz de la lista)
            ->holders()
            // 3. Excluimos todas las cuentas (acc) donde el TITULAR sea Tesorería o Desocupado
            ->whereNotIn('acc', function ($query) {
                $query->select('acc')
                    ->from('0cc_socios')
                    ->where('categoria', PartnerCategory::TITULAR->value)
                    ->where(function ($q) {
                        $q->where('nombre', 'LIKE', '%TESORERIA%')
                            ->orWhere('nombre', 'LIKE', '%DESOCUPADO%');
                    });
            })
            // 4. Filtro de seguridad individual por si acaso
            ->where('nombre', 'NOT LIKE', '%TESORERIA%')
            ->where('nombre', 'NOT LIKE', '%DESOCUPADO%')
            ->get();
    }

    /**
     * Tomar socios mas el total de invitados del mes actual
     */
    /**
     * Retorna una lista con la cuenta (acc) y el total de invitados del mes actual,
     * solo para socios Titulares que no estén marcados como DESOCUPADO.
     */
    public function getGuestCountThisMonth()
    {
        return Partner::query()
            ->select('acc') // Solo necesitamos cargar este campo en memoria
            ->holders() // Filtra por PartnerCategory::TITULAR
            ->where('nombre', 'NOT LIKE', '%DESOCUPADO%')
            // Contamos la relación 'invitations' aplicando el scope 'currentMonth' del modelo Guest
            ->withCount(['invitations as count_guest' => function ($query) {
                $query->currentMonth();
            }])
            ->get()
            // Mapeamos para retornar estrictamente un arreglo con los dos campos solicitados
            ->map(function ($partner) {
                return [
                    'acc' => $partner->acc,
                    'count_guest' => $partner->count_guest,
                ];
            });
    }
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
