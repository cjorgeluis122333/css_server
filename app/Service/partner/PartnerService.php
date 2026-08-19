<?php

namespace App\Service\partner;
use App\Enum\PartnerCategory;
use App\Models\partners\Partner;
use App\Service\photo\PhotoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PartnerService
{

    protected PhotoService $photoService;

    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;
    }
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
     * Retorna una lista con la cuenta (acc) y el total de invitados de un mes dado,
     * solo para socios Titulares que no estén marcados como DESOCUPADO.
     *
     * @param string|null $month Fecha en formato yyyy-MM (ej: 2026-01). Si es null, usa el mes actual.
     */
    public function getGuestCountByMonth(?string $month = null)
    {
        $date = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        return Partner::query()
            ->select('acc')
            ->holders()
            ->where('nombre', 'NOT LIKE', '%DESOCUPADO%')
            ->withCount(['invitations as count_guest' => function ($query) use ($date) {
                $query->byMonth($date->year, $date->month);
            }])
            ->get()
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
     * Elimina o limpia los datos de un socio según su categoría y limpia sus imágenes de DNI.
     *
     * @param Partner $partner
     * @return void
     * @throws Exception
     */
    public function deletePartner(Partner $partner): void
    {
        DB::transaction(function () use ($partner) {
            if ($partner->isHolder()) {
                // 1. SI ES TITULAR:

                // a) Borramos las imágenes DNI de todos los familiares asociados
                $dependents = $partner->dependents()->get();
                foreach ($dependents as $dependent) {
                    if (!empty($dependent->cedula)) {
                        $this->photoService->deleteDniImages($dependent->cedula);
                    }
                }

                // b) Borramos las imágenes DNI del propio titular (si tiene cédula)
                if (!empty($partner->cedula)) {
                    $this->photoService->deleteDniImages($partner->cedula);
                }

                // c) Eliminamos de la base de datos a los socios familiares
                $partner->dependents()->delete();

                // d) Reseteamos los campos fillable del titular excepto 'acc' y 'categoria'
                $fieldsToReset = array_diff($partner->getFillable(), ['acc', 'categoria']);

                $updateData = [];
                foreach ($fieldsToReset as $field) {
                    $updateData[$field] = null;
                }

                $partner->update($updateData);

            } else {
                // 2. SI ES FAMILIAR:

                // a) Borramos sus imágenes DNI (si tiene cédula)
                if (!empty($partner->cedula)) {
                    $this->photoService->deleteDniImages($partner->cedula);
                }

                // b) Eliminamos físicamente el registro del familiar
                $partner->delete();
            }
        });
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
