<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartnerRequest;
use App\Models\Partner;
use App\Service\PartnerService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PartnerController extends Controller
{
    use ApiResponse;

    protected PartnerService $partnerService;

    public function __construct(PartnerService $partnerService)
    {
        $this->partnerService = $partnerService;
    }
    // Importamos el trait de respuestas

    /**
     * Campos ligeros para el listado masivo (Index).
     * Evitamos traer campos pesados como 'direccion' o 'notas' si las hubiera.
     */
    protected array $selectIndex = [
        'ind', 'acc', 'nombre', 'cedula', 'celular', 'correo', 'nacimiento', 'categoria'
    ];


    /**
     * GET /api/partners
     * Parameters:  (page, per_page)
     */
    public function index(Request $request)
    {
        // Seleccionamos solo lo necesario directamente en la consulta
        $partners = Partner::holders()
            ->select(['ind', 'acc', 'nombre', 'cedula', 'celular', 'correo', 'nacimiento', 'categoria'])
            ->orderBy('acc', 'asc')
            ->paginate($request->input('per_page', 50));


        return response()->json($partners);
    }

    /**
     * GET /api/partners/{id}
     * Muestra el detalle completo de un Titular.
     */
    public function show($id)
    {
        // Buscamos por ID ('ind') pero aseguramos que sea TITULAR.
        // Si el ID existe pero es un FAMILIAR, devolverá 404 (seguridad).
        $partner = Partner::holders()
            ->where('acc', $id)
            ->first();

        if (!$partner) {
            return $this->errorResponse('Socio titular no encontrado', 404);
        }

        return $this->successResponse($partner);
    }

    /**
     * POST /api/partners
     * Crea un nuevo Socio Titular.
     */
    public function store(PartnerRequest $request)
    {
        try {
            // Solo enviamos los datos ya validados por el PartnerRequest
            $partner = $this->partnerService->createTitular($request->validated());

            return $this->successResponse(
                $partner,
                'Socio titular creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear socio: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/partners/{acc}
     * Actualiza usando la ACC como identificador en la URL.
     */
    public function update(PartnerRequest $request, $acc)
    {
        // 1. Buscamos al socio por su ACC y aseguramos que sea TITULAR.
        // Si no existe, firstOrFail lanza 404 automáticamente (o puedes usar if tradicional).
        try {
            $partner = Partner::holders()->where('acc', $acc)->firstOrFail();

            // 2. Delegamos la actualización al servicio
            $updatedPartner = $this->partnerService->updateTitular(
                $partner,
                $request->validated()
            );

            return $this->successResponse($updatedPartner, 'Socio titular actualizado con éxito');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Socio titular no encontrado con esa Acción', 404);
        } catch (\Exception $e) {
            Log::error("Error update partner {$acc}: " . $e->getMessage());
            return $this->errorResponse('No se pudo actualizar el socio', 500);
        }
    }
    /**
     * DELETE /api/partners/{id}
     * Elimina un socio (Hard Delete).
     */
    public function destroy($acc)
    {
        try {
            // Buscamos por ACC antes de intentar borrar
            $partner = Partner::holders()->where('acc', $acc)->firstOrFail();

            $this->partnerService->deleteTitular($partner);

            return $this->successResponse(null, 'Socio eliminado permanentemente');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Socio no encontrado', 404);
        } catch (\Exception $e) {
            // Capturamos la excepción de negocio (ej: tiene familiares)
            // Asumimos que el código 409 es para conflicto de lógica
            return $this->errorResponse($e->getMessage(), 409);
        }
    }
}
