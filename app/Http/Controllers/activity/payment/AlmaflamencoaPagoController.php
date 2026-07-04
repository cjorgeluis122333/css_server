<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\AlmaflamencoaPagoRequest;
use App\Http\Requests\activity\StoreAlmaflamencoaPagoRequest;
use App\Service\activity\payment\AlmaflamencoaPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AlmaflamencoaPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AlmaflamencoaPagoService $almaflamencoaPagoService
    ) {}

    public function index(AlmaflamencoaPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->almaflamencoaPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Alma Flamenca.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Alma Flamenca.', 500);
        }
    }

    public function showByMes(AlmaflamencoaPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->almaflamencoaPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Alma Flamenca para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Alma Flamenca.', 500);
        }
    }

    public function store(StoreAlmaflamencoaPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->almaflamencoaPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Alma Flamenca registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
