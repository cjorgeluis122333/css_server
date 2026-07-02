<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StrongPagoRequest;
use App\Http\Requests\activity\StoreStrongPagoRequest;
use App\Service\activity\StrongPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class StrongPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StrongPagoService $strongPagoService
    ) {}

    public function index(StrongPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->strongPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Strong.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Strong.', 500);
        }
    }

    public function showByMes(StrongPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->strongPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Strong para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Strong.', 500);
        }
    }

    public function store(StoreStrongPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->strongPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Strong registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
