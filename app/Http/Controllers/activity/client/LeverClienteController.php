<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreLeverClienteRequest;
use App\Http\Resources\activity\client\LeverClienteResource;
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

    public function store(StoreLeverClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->leverClienteService->create($request->validated());

            return $this->successResponse(new LeverClienteResource($cliente), 'Cliente de Lever registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
