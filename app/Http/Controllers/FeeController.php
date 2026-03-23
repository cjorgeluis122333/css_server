<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeRequest;
use App\Models\Fee;
use App\Service\FeeService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class FeeController extends Controller
{
    use ApiResponse;

    protected FeeService $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    public function index(): JsonResponse
    {
        $fees = $this->feeService->getAll();
        return $this->successResponse($fees, 'Lista de cuotas recuperada.');
    }

    public function showByMonth(?string $mes = null): JsonResponse
    {
        // Esto arregla el problema de "2026-1" -> "2026-01"
        try {
            $mesBusqueda = $mes ? Carbon::parse($mes)->format('Y-m') : now()->format('Y-m');
        } catch (\Exception $e) {
            return $this->errorResponse("Formato de fecha inválido.", 400);
        }

        $fee = $this->feeService->getByMonth($mesBusqueda);

        if (!$fee) {
            return $this->errorResponse("No se encontró la cuota para el mes {$mesBusqueda}.", 404);
        }

        return $this->successResponse($fee, "Información de la cuota {$mesBusqueda}.");
    }

    public function store(FeeRequest $request): JsonResponse
    {
        $fee = $this->feeService->store($request->validated());
        return $this->successResponse($fee, 'Cuota creada correctamente.', 201);
    }

    public function update(FeeRequest $request, int $id): JsonResponse
    {
        $this->feeService->update($id, $request->validated());
        return $this->successResponse(null, 'Cuota actualizada correctamente.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->feeService->delete($id);
        return $this->successResponse(null, 'Cuota eliminada correctamente.');
    }
}
