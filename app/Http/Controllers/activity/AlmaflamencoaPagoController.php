<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\AlmaflamencoaPagoRequest;
use App\Service\activity\AlmaflamencoaPagoService;
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
}
