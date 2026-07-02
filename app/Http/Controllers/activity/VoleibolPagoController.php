<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\VoleibolPagoRequest;
use App\Http\Requests\activity\StoreVoleibolPagoRequest;
use App\Service\activity\VoleibolPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VoleibolPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VoleibolPagoService $voleibolPagoService
    ) {}

    public function index(VoleibolPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->voleibolPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de voleibol.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de voleibol.', 500);
        }
    }

    public function showByMes(VoleibolPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->voleibolPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de voleibol para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de voleibol.', 500);
        }
    }

    public function store(StoreVoleibolPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->voleibolPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de voleibol registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
