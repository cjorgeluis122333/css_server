<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\StrongClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class StrongClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StrongClienteService $strongClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->strongClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Strong.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Strong.', 500);
        }
    }
}
