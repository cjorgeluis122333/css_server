<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\VoleibolClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VoleibolClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VoleibolClienteService $voleibolClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->voleibolClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Voleibol.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Voleibol.', 500);
        }
    }
}
