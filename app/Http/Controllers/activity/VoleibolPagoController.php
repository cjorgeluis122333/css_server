<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\VoleibolPagoRequest;
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
}
