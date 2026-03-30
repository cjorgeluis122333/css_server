<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisteredGuestRequest;
use App\Models\RegisteredGuest;
use App\Service\RegisteredGuestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class RegisteredGuestController extends Controller
{
    use ApiResponse;

    protected RegisteredGuestService $registeredGuestService;

    public function __construct(RegisteredGuestService $registeredGuestService)
    {
        $this->registeredGuestService = $registeredGuestService;
    }

    /**
     * Lista todos los invitados registrados (paginados).
     */
    public function index(): JsonResponse
    {
        return $this->successResponse(RegisteredGuest::all(), "Catálogo de invitados recuperado.");
    }


    /**
     * Almacena un nuevo invitado en el catálogo manualmente.
     */
    public function store(RegisteredGuestRequest $request): JsonResponse
    {
        try {
            $guest = $this->registeredGuestService->createGuest($request->validated());
            return $this->successResponse($guest, "Invitado registrado en el catálogo correctamente.", 201);
        } catch (Exception $e) {
            return $this->errorResponse("Error al crear el invitado: " . $e->getMessage(), 500);
        }
    }


    /**
     * Actualiza los datos de un invitado existente en el catálogo.
     */
    public function update(RegisteredGuestRequest $request, int $ind): JsonResponse
    {
        try {
            $guest = $this->registeredGuestService->updateGuest($ind, $request->validated());
            return $this->successResponse($guest, "Datos del invitado actualizados correctamente.");
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return $this->errorResponse("No se pudo actualizar el invitado: " . $e->getMessage(), $statusCode);
        }
    }

    /**
     * Elimina un invitado del catálogo.
     */
    public function destroy(int $ind): JsonResponse
    {
        try {
            $this->registeredGuestService->deleteGuest($ind);
            return $this->successResponse(null, "Invitado eliminado del catálogo correctamente.");
        } catch (Exception $e) {
            return $this->errorResponse("No se pudo eliminar el registro.", 500);
        }
    }
}
