<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\KarateClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class KarateClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected KarateClienteService $karateClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->karateClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Karate.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Karate.', 500);
        }
    }
}
