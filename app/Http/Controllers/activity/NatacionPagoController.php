<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\NatacionPagoRequest;
use App\Http\Requests\activity\StoreNatacionPagoRequest;
use App\Service\activity\NatacionPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NatacionPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NatacionPagoService $natacionPagoService
    ) {}

    public function index(NatacionPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->natacionPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de natación.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de natación.', 500);
        }
    }

    public function showByMes(NatacionPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->natacionPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de natación para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de natación.', 500);
        }
    }

    public function store(StoreNatacionPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->natacionPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de natación registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
