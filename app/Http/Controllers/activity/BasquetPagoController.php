<?php

namespace App\Http\Controllers\activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\BasquetPagoRequest;
use App\Service\activity\BasquetPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BasquetPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BasquetPagoService $basquetPagoService
    ) {}

    public function index(BasquetPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->basquetPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de básquet.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de básquet.', 500);
        }
    }
}
