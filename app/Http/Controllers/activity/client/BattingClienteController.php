<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\BattingClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BattingClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BattingClienteService $battingClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->battingClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Batting.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Batting.', 500);
        }
    }
}
