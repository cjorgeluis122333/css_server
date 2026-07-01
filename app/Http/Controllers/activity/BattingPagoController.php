<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\BattingPagoRequest;
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
}
