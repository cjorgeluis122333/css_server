<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\NatacionPagoRequest;
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
}
