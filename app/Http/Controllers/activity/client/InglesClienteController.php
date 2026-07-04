<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\InglesClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InglesClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InglesClienteService $inglesClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->inglesClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Ingles.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Ingles.', 500);
        }
    }
}
