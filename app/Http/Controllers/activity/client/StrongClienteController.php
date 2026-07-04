<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreStrongClienteRequest;
use App\Http\Resources\activity\client\StrongClienteResource;
use App\Service\activity\client\StrongClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class StrongClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StrongClienteService $strongClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->strongClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Strong.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Strong.', 500);
        }
    }

    public function store(StoreStrongClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->strongClienteService->create($request->validated());

            return $this->successResponse(new StrongClienteResource($cliente), 'Cliente de Strong registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
