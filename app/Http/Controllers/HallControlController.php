<?php

namespace App\Http\Controllers;

use App\Http\Requests\HallControlRequest;
use App\Http\Resources\HallControlResource;
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
            return $this->successResponse(HallControlResource::collection($register), 'Registros obtenidos correctamente.');
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error al obtener los registros.', 500);
        }
    }

    public function recentHistory()
    {
        try {
            $history = $this->salonService->getRecentHistory();

            return $this->successResponse(
                HallControlResource::collection($history),
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
            return $this->successResponse(new HallControlResource($register), 'Registro creado exitosamente.', 201);
        } catch (Exception $e) {
            \Log::error('HallControl Store Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'validated_data' => $request->validated(),
            ]);
            return $this->errorResponse('Error al crear registro: ' . $e->getMessage(), 500);
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

        return $this->successResponse(new HallControlResource($register), 'Registro obtenido correctamente.');
    }

    /**
     * Permite modificar los valores del formulario pasando por tus validaciones.
     */
    public function update(HallControlRequest $request, int $id): JsonResponse
    {
        try {
            $salon = $this->salonService->getById($id);

            if (!$salon) {
                return $this->errorResponse('Salón no encontrado.', 404);
            }

            // Pasamos los datos validados al servicio
            $updatedSalon = $this->salonService->update($salon, $request->validated());
            
            return $this->successResponse(new HallControlResource($updatedSalon), 'Salón actualizado correctamente.');
        } catch (Exception $e) {
            \Log::error('HallControl Update Error', [
                'id'      => $id,
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('Ocurrió un error al actualizar el salón.', 500);
        }
    }

    /**
     * Pone todos los campos en su estado inicial (Disponible).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $salon = $this->salonService->getById($id);

            if (!$salon) {
                return $this->errorResponse('Salón no encontrado.', 404);
            }

            // Llamamos al método que resetea los campos
            $resetSalon = $this->salonService->resetToAvailable($salon);

            return $this->successResponse($resetSalon, 'El salón ha sido liberado y puesto en su estado inicial.');
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error al liberar el salón.', 500);
        }
    }
}
