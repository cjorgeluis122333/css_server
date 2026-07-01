<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\LeverPagoRequest;
use App\Service\activity\LeverPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeverPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeverPagoService $leverPagoService
    ) {}

    public function index(LeverPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->leverPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Lever.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Lever.', 500);
        }
    }
}
