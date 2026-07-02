<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\KaratePagoRequest;
use App\Http\Requests\activity\StoreKaratePagoRequest;
use App\Service\activity\KaratePagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class KaratePagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected KaratePagoService $karatePagoService
    ) {}

    public function index(KaratePagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->karatePagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de karate.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de karate.', 500);
        }
    }

    public function showByMes(KaratePagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->karatePagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de karate para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de karate.', 500);
        }
    }

    public function store(StoreKaratePagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->karatePagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de karate registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
