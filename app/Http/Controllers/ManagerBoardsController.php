<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagerBoardsRequest;
use App\Http\Resources\ManagerBoardsResource;
use App\Service\ManagerBoardsService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ManagerBoardsController extends Controller
{
    use ApiResponse;

    protected ManagerBoardsService $managerBoardsService;

    public function __construct(ManagerBoardsService $managerBoardsService)
    {
        $this->managerBoardsService = $managerBoardsService;
    }

    public function index(): JsonResponse
    {
        try {
            $boards = $this->managerBoardsService->getAll();

            return $this->successResponse(ManagerBoardsResource::collection($boards), 'Juntas obtenidas con éxito');
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener las juntas: '.$e->getMessage(), 500);
        }
    }

    public function store(ManagerBoardsRequest $request): JsonResponse
    {
        try {
            $board = $this->managerBoardsService->upsertBoard($request->validated());

            return $this->successResponse(
                new ManagerBoardsResource($board),
                "Junta del año {$board->year} guardada exitosamente",
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error al procesar la junta: '.$e->getMessage(), 500);
        }
    }

    public function show(int $year): JsonResponse
    {
        try {
            $board = $this->managerBoardsService->getByYear($year);

            return $this->successResponse($board);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la junta: '.$e->getMessage(), 500);
        }
    }

    public function update(ManagerBoardsRequest $request, int $year): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['year'] = $year;

            $updatedBoard = $this->managerBoardsService->upsertBoard($data);

            return $this->successResponse(
                new ManagerBoardsResource($updatedBoard),
                'Reunión de directivos actualizada exitosamente'
            );

        } catch (Exception $e) {
            return $this->errorResponse('Error al procesar la actualización: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $year): JsonResponse
    {
        try {
            $this->managerBoardsService->delete($year);

            return $this->successResponse(null, 'Reunion de directivos eliminada exitosamente');

        } catch (ModelNotFoundException) {
            return $this->errorResponse('Reunion de directivos no encontrada', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al procesar la eliminación: '.$e->getMessage(), 500);
        }
    }
}
