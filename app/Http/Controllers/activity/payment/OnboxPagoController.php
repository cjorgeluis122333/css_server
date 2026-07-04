<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\OnboxPagoRequest;
use App\Http\Requests\activity\StoreOnboxPagoRequest;
use App\Service\activity\payment\OnboxPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OnboxPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OnboxPagoService $onboxPagoService
    ) {}

    public function index(OnboxPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->onboxPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Onbox.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Onbox.', 500);
        }
    }

    public function showByMes(OnboxPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->onboxPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Onbox para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Onbox.', 500);
        }
    }

    public function store(StoreOnboxPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->onboxPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Onbox registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
