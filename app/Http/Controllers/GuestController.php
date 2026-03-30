<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestRequest;
use App\Service\GuestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Exception;

class GuestController extends Controller
{
    use ApiResponse;

    protected GuestService $guestService;

    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    /**
     * Lista los invitados paginados por año y agrupados por mes.
     */
    public function index(int $acc): JsonResponse
    {
        $paginatedData = $this->guestService->getGuestsPaginatedByYear($acc);

        return response()->json([
            'success' => true,
            'data' => $paginatedData
        ]);
    }

    /**
     * Almacena un nuevo invitado.
     */
    public function store(GuestRequest $request): JsonResponse
    {
        try {
            $guest = $this->guestService->createGuest($request->validated());
            return $this->successResponse($guest, "Invitado registrado correctamente.", 201);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }


    /**
     * Retorna los invitados del socio para el mes y año actual.
     */
    public function currentMonth(int $acc): JsonResponse
    {
        $guests = $this->guestService->getCurrentMonthGuests($acc);
        return $this->successResponse($guests, "Invitados del mes actual recuperados.");
    }

    /**
     * Actualiza los datos de un invitado.
     */
    public function update(GuestRequest $request, int $ind): JsonResponse
    {
        try {
            $guest = $this->guestService->updateGuest($ind, $request->validated());
            return $this->successResponse($guest, "Invitado actualizado correctamente.");
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Elimina un invitado.
     */
    public function destroy(int $ind): JsonResponse
    {
        try {
            $this->guestService->deleteGuest($ind);
            return $this->successResponse(null, "Invitado eliminado correctamente.");
        } catch (Exception $e) {
            return $this->errorResponse("No se pudo eliminar el registro.", 500);
        }
    }
}
