<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\InglesPagoRequest;
use App\Service\activity\InglesPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InglesPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InglesPagoService $inglesPagoService
    ) {}

    public function index(InglesPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->inglesPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de inglés.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de inglés.', 500);
        }
    }
}
