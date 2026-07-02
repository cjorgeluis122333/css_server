<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\PinponPagoRequest;
use App\Http\Requests\activity\StorePinponPagoRequest;
use App\Service\activity\PinponPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PinponPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PinponPagoService $pinponPagoService
    ) {}

    public function index(PinponPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->pinponPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Pin Pon.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Pin Pon.', 500);
        }
    }

    public function showByMes(PinponPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->pinponPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Pin Pon para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Pin Pon.', 500);
        }
    }

    public function store(StorePinponPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->pinponPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Pin Pon registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
