<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\OnboxClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OnboxClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OnboxClienteService $onboxClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->onboxClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Onbox.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Onbox.', 500);
        }
    }
}
