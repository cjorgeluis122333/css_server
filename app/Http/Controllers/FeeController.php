<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeRequest;
use App\Models\Fee;
use App\Service\FeeService;
use App\Traits\ApiResponse;
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

    public function showByMonth(string $mes): JsonResponse
    {
        $fee = $this->feeService->getByMonth($mes);

        if (!$fee) {
            return $this->errorResponse("No se encontró la cuota para el mes {$mes}.", 404);
        }

        return $this->successResponse($fee, "Información de la cuota {$mes}.");
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
