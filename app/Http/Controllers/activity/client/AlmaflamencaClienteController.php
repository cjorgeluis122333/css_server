<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\AlmaflamencaClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AlmaflamencaClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AlmaflamencaClienteService $almaflamencaClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->almaflamencaClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Alma Flamenca.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Alma Flamenca.', 500);
        }
    }
}
