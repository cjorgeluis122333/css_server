<?php

namespace App\Http\Controllers;

use App\Http\Requests\FamilyRequest;
use App\Models\Partner;
use App\Service\PartnerService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FamilyController extends Controller
{
    use ApiResponse;

    protected PartnerService $partnerService;
    private array $familyColumns = [
        'ind', 'acc', 'nombre', 'cedula',
        'carnet', 'celular', 'nacimiento',
        'direccion', 'categoria','telefono'
    ];
    public function __construct(PartnerService $partnerService)
    {
        $this->partnerService = $partnerService;
    }

    /**
     * GET /api/families
     * Lista familiares. Permite filtrar por ?acc=123
     */
    public function index()
    {
        $families = Partner::onlyDependents()
            ->select($this->familyColumns)
            ->orderBy('acc', 'asc')
            ->get();

        return  $this->successResponse($families,"Lista de todos los familiares");
    }
    /**
     * GET /api/families/{id}
     */
    public function show($acc)
    {
        $families = Partner::onlyDependents()
            ->where('acc', $acc)
            ->select($this->familyColumns)
            ->get();

        if (!$families) {
            return $this->errorResponse('Familiar no encontrado', 404);
        }

        return $this->successResponse($families);
    }

    /**
     * POST /api/families
     * El PartnerRequest asegura que la 'acc' exista y sea de un Titular.
     */
    public function store(FamilyRequest $request)
    {
        try {
            $familiar = $this->partnerService->createFamiliar($request->validated());
            return $this->successResponse($familiar, 'Familiar creado exitosamente', 201);
        } catch (Exception $e) {
            Log::error('Error store familiar: ' . $e->getMessage());
            return $this->errorResponse('Error al crear familiar', 500);
        }
    }

    /**
     * PUT /api/families/{id}
     */
    public function update(FamilyRequest $request, $id)
    {
        try {
            $familiar = Partner::onlyDependents()->findOrFail($id);

            $updatedFamiliar = $this->partnerService->updateFamiliar(
                $familiar,
                $request->validated()
            );

            return $this->successResponse($updatedFamiliar, 'Familiar actualizado con éxito');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Familiar no encontrado', 404);
        } catch (Exception $e) {
            Log::error("Error update familiar {$id}: " . $e->getMessage());
            return $this->errorResponse('No se pudo actualizar el familiar', 500);
        }
    }

    /**
     * DELETE /api/families/{id}
     */
    public function destroy($id)
    {
        try {
            $familiar = Partner::onlyDependents()->findOrFail($id);
            $this->partnerService->deleteFamiliar($familiar);

            return $this->successResponse(null, 'Familiar eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Familiar no encontrado', 404);
        } catch (Exception $e) {
            Log::error("Error delete familiar {$id}: " . $e->getMessage());
            return $this->errorResponse('Error al eliminar familiar', 500);
        }
    }
}
