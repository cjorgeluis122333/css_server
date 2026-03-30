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
}
