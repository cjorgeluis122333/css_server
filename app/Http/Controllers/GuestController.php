<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Http\Requests\GuestRequest;
use App\Http\Resources\GuestResource;
use App\Models\Guest;
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
    public function index(?int $acc = null): JsonResponse
    {
        $user = auth()->user();

        if ($acc === null) {
            $acc = $user->acc;
        }

        // PARTNER/HONORARY solo pueden consultar su propia acción
        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY) && $user->acc !== $acc) {
            return $this->errorResponse('Solo puedes consultar invitados de tu propia acción.', 403);
        }

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
            $user = auth()->user();
            $data = $request->validated();

            // PARTNER/HONORARY solo pueden registrar invitados a nombre de su propia acción
            if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY) && (int) $data['acc'] !== $user->acc) {
                return $this->errorResponse('Solo puedes registrar invitados para tu propia acción.', 403);
            }

            $guest = $this->guestService->createGuest($data);
            return $this->successResponse(new GuestResource($guest), "Invitado registrado correctamente.", 201);
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
        $user = auth()->user();

        if ($user->hasRole(UserRole::PARTNER, UserRole::HONORARY) && $user->acc !== $acc) {
            return $this->errorResponse('Solo puedes consultar invitados de tu propia acción.', 403);
        }

        $guests = $this->guestService->getCurrentMonthGuests($acc);
        return $this->successResponse(GuestResource::collection($guests), "Invitados del mes actual recuperados.");
    }

    /**
     * Actualiza los datos de un invitado.
     */
    public function update(GuestRequest $request, int $ind): JsonResponse
    {
        try {
            $guest = Guest::findOrFail($ind);
            $this->authorize('update', $guest);

            $updatedGuest = $this->guestService->updateGuest($ind, $request->validated());
            return $this->successResponse(new GuestResource($updatedGuest), "Invitado actualizado correctamente.");
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
            $guest = Guest::findOrFail($ind);
            $this->authorize('delete', $guest);

            $this->guestService->deleteGuest($ind);
            return $this->successResponse(null, "Invitado eliminado correctamente.");
        } catch (Exception $e) {
            return $this->errorResponse("No se pudo eliminar el registro.", 500);
        }
    }
}
