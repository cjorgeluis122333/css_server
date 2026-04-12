<?php

namespace App\Http\Controllers;

use App\Http\Requests\HallControlRequest;
use App\Service\HallControlService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class HallControlController extends Controller
{
    use ApiResponse;

    protected HallControlService $salonService;

    public function __construct(HallControlService $salonService)
    {
        $this->salonService = $salonService;
    }

    /**
     * Listar todos los registros.
     */
    public function index(): JsonResponse
    {
        try {
            $register = $this->salonService->getAll();
            return $this->successResponse($register, 'Registros obtenidos correctamente.');
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error al obtener los registros.', 500);
        }
    }

    public function recentHistory()
    {
        try {
            $history = $this->salonService->getRecentHistory();

            return $this->successResponse(
                $history,
                'Historial de salones obtenido correctamente.'
            );
        } catch (Exception $e) {
            // Puedes usar $e->getMessage() para depurar si lo necesitas en entorno de desarrollo
            return $this->errorResponse('Ocurrió un error al obtener el historial de los salones.', 500);
        }
    }

    /**
     * Crear un nuevo registro.
     */
    public function store(HallControlRequest $request): JsonResponse
    {
        try {
            // El request ya viene validado aquí
            $register = $this->salonService->create($request->validated());
            return $this->successResponse($register, 'Registro creado exitosamente.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error al crear el registro.', 500);
        }
    }

    /**
     * Mostrar un registro específico.
     */
    public function show(int $id): JsonResponse
    {
        $register = $this->salonService->getById($id);

        if (!$register) {
            return $this->errorResponse('Registro no encontrado.', 404);
        }

        return $this->successResponse($register, 'Registro obtenido correctamente.');
    }

    /**
     * Actualizar un registro específico.
     */
    public function update(HallControlRequest $request, int $id): JsonResponse
    {
        try {
            $register = $this->salonService->getById($id);

            if (!$register) {
                return $this->errorResponse('Registro no encontrado.', 404);
            }

            $registerUpdate = $this->salonService->update($register, $request->validated());
            return $this->successResponse($registerUpdate, 'Registro actualizado exitosamente.');
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error al actualizar el registro.', 500);
        }
    }

    /**
     * Eliminar un registro específico.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $register = $this->salonService->getById($id);

            if (!$register) {
                return $this->errorResponse('Registro no encontrado.', 404);
            }

            $this->salonService->delete($register);
            return $this->successResponse(null, 'Registro eliminado exitosamente.');
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error al eliminar el registro.', 500);
        }
    }
}
