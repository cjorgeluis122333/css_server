<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagerBoardsRequest;
use App\Http\Resources\ManagerBoardsResource;
use App\Models\ManagerBoards;
use App\Service\BoardsService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $boards = ManagerBoards::with([
            'rel_presidente', 'rel_vicepresidente', 'rel_secretario',
            'rel_vicesecretario', 'rel_tesorero', 'rel_vicetesorero',
            'rel_bibliotecario', 'rel_actas', 'rel_viceactas',
            'rel_actos', 'rel_deportes', 'rel_vocal1', 'rel_vocal2'
        ])->get();


         return $this->successResponse(ManagerBoardsResource::collection($boards), 'Juntas obtenidas con éxito');
    }

    public function store(ManagerBoardsRequest $request)
    {
        $data = $request->validated();
        $year = $data['year'];
        if (ManagerBoards::where("year", $year)->exists()) {
            return $this->errorResponse("Ya existe una reunión registrada en el año {$year}", 409);
        }

        $board = $this->boardService->saveBoard($data);

        return $this->successResponse(
            $board,
            "Junta del año {$year} guardada exitosamente",
            201
        );
    }

    public function show(int $year)
    {
        try {
            $board = ManagerBoards::where("year", $year)->firstOrFail();
            return $this->successResponse($board);
        } catch (ModelNotFoundException) {
            return $this->errorResponse("Reunion de directivos no encontrada", 404);
        } catch (Exception $e) {
            return $this->errorResponse("Error al procesar la junta: " . $e->getMessage(), 500);
        }

    }


    public function update(ManagerBoardsRequest $request, $year)
    {
        try {
            ManagerBoards::findOrFail($year);
            $managerBoards = $request->validated();
            $managerBoards->update($managerBoards);
            return $this->successResponse($managerBoards, "Reunion de directivos actualizada exitosamente");
        } catch (ModelNotFoundException) {
            return $this->errorResponse("Reunion de directivos no encontrada", 404);
        } catch (Exception $e) {
            return $this->errorResponse("Error al procesar la junta: " . $e->getMessage(), 500);
        }
    }


    function destroy(int $year)
    {
        try {
            $board = managerBoards::findOrFail($year);
            $board->delete();
            return $this->successResponse("Reunion de directivos eliminada exitosamente");

        } catch (ModelNotFoundException) {
            return $this->errorResponse("Reunion de directivos no encontrada", 404);

        } catch (Exception $e) {
            return $this->errorResponse("Error al procesar la junta: " . $e->getMessage(), 500);
        }

    }
}
