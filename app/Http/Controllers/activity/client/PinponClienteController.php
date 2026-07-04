<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StorePinponClienteRequest;
use App\Http\Resources\activity\client\PinponClienteResource;
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

    public function store(StorePinponClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->pinponClienteService->create($request->validated());

            return $this->successResponse(new PinponClienteResource($cliente), 'Cliente de Pin Pon registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
