<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\NatacionClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NatacionClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NatacionClienteService $natacionClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->natacionClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de natacion.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de natacion.', 500);
        }
    }
}
