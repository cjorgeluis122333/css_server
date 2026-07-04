<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Service\activity\client\PinponClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PinponClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PinponClienteService $pinponClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->pinponClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Pin Pon.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Pin Pon.', 500);
        }
    }
}
