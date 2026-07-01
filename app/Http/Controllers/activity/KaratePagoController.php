<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\KaratePagoRequest;
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
}
