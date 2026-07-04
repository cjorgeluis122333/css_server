<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\BasquetClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BasquetClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BasquetClienteService $basquetClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->basquetClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Basquet.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Basquet.', 500);
        }
    }
}
