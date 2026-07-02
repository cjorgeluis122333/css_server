<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\BattingPagoRequest;
use App\Http\Requests\activity\StoreBattingPagoRequest;
use App\Service\activity\BattingPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BattingPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BattingPagoService $battingPagoService
    ) {}

    public function index(BattingPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->battingPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de batting.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de batting.', 500);
        }
    }

    public function showByMes(BattingPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->battingPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de batting para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de batting.', 500);
        }
    }

    public function store(StoreBattingPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->battingPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de batting registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
