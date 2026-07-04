<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\LeverClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeverClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeverClienteService $leverClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->leverClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Lever.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Lever.', 500);
        }
    }
}
