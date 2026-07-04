<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\InglesPagoRequest;
use App\Http\Requests\activity\StoreInglesPagoRequest;
use App\Service\activity\payment\InglesPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InglesPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InglesPagoService $inglesPagoService
    ) {}

    public function index(InglesPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->inglesPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de inglés.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de inglés.', 500);
        }
    }

    public function showByMes(InglesPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->inglesPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de inglés para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de inglés.', 500);
        }
    }

    public function store(StoreInglesPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->inglesPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de inglés registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
