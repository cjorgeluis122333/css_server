<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Http\Requests\ManagerRequest;
use App\Service\ManagerService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Exception;

class ManagerController extends Controller
{
    use ApiResponse;

    protected ManagerService $managerService;

    // Inject the service
    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * Listar todos los directivos.
     */
    public function index(): JsonResponse
    {
        try {
            $managers = $this->managerService->getAllManagers();
            return $this->successResponse($managers, 'Lista de directivos obtenida con éxito.');
        } catch (Exception) {
            return $this->errorResponse('Error al obtener la lista de directivos.', 500);
        }
    }

    /**
     * Guardar un nuevo directivo.
     */
    public function store(ManagerRequest $request): JsonResponse
    {
        try {
            $manager = $this->managerService->createManager($request->validated());
            return $this->successResponse($manager, 'Directivo creado correctamente.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('No se pudo crear el directivo.', 500);
        }
    }

    /**
     * Mostrar un directivo específico.
     */
    public function show($acc): JsonResponse
    {
        try {
            $manager = Manager::where("acc" ,$acc)->firstOrFail();
            return $this->successResponse($manager, 'Detalles del directivo obtenidos.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Directivo no encontrado.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener el directivo.', 500);
        }
    }

    /**
     * Actualizar un directivo existente.
     */
    public function update(ManagerRequest $request, $acc): JsonResponse
    {
        try {
            $manager = Manager::where("acc",$acc)->firstOrFail();
            $updatedManager = $this->managerService->updateManager($manager, $request->validated());
            return $this->successResponse($updatedManager, 'Directivo actualizado con éxito.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('El directivo no se encontro en la vase de datos', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al intentar actualizar el directivo.', 500);
        }
    }

    /**
     * Eliminar un directivo.
     */
    public function destroy($acc): JsonResponse
    {
        try {
            $manager = Manager::where('acc',$acc)->firstOrFail();
            $this->managerService->deleteManager($manager);
            return $this->successResponse(null, 'Directivo eliminado correctamente.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('El directivo no se pudo eliminar', 404);
        } catch (Exception $e) {
            return $this->errorResponse('No se pudo eliminar el directivo.', 500);
        }
    }
}
