<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagerBoardsRequest;
use App\Http\Resources\ManagerBoardsResource;
use App\Models\ManagerBoards;
use App\Service\BoardsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ManagerBoardsController extends Controller
{
    use ApiResponse;

    protected BoardsService $boardService;

    public function __construct(BoardsService $boardService)
    {
        $this->boardService = $boardService;
    }

    public function index()
    {
        return ManagerBoardsResource::collection(ManagerBoards::all());
    }

    public function store(ManagerBoardsRequest $request): JsonResponse
    {
        try {
            $board = $this->boardService->saveBoard($request->validated());
            return $this->successResponse($board, "Junta del año {$board->year} guardada exitosamente", 201);
        } catch (\Exception $e) {
            return $this->errorResponse("Error al procesar la junta: " . $e->getMessage(), 500);
        }
    }

    public function show(int $year): JsonResponse
    {
        $board = $this->boardService->getBoardWithNames($year);

        if (!$board) {
            return $this->errorResponse("No se encontró junta para el año $year", 404);
        }

        return $this->successResponse($board);
    }


    public function update(ManagerBoardsRequest $request, ManagerBoards $managerBoards)
    {
        $managerBoards->update($request->validated());

        return new ManagerBoardsResource($managerBoards);
    }

    public function destroy(ManagerBoards $managerBoards)
    {
        $managerBoards->delete();

        return response()->json();
    }
}
