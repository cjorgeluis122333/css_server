<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\OnboxPagoRequest;
use App\Service\activity\OnboxPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OnboxPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OnboxPagoService $onboxPagoService
    ) {}

    public function index(OnboxPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->onboxPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Onbox.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Onbox.', 500);
        }
    }
}
