<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreBasquetClienteRequest;
use App\Http\Resources\activity\client\BasquetClienteResource;
use App\Service\activity\client\BasquetClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BasquetClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BasquetClienteService $basquetClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->basquetClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Basquet.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Basquet.', 500);
        }
    }

    public function store(StoreBasquetClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->basquetClienteService->create($request->validated());

            return $this->successResponse(new BasquetClienteResource($cliente), 'Cliente de Basquet registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function showByCedula(string $cedula): JsonResponse
    {
        try {
            $cliente = $this->basquetClienteService->findByCedula($cedula);

            if (! $cliente) {
                return $this->errorResponse('No se encontró una coincidencia.', 404);
            }

            return $this->successResponse(new BasquetClienteResource($cliente), 'Cliente encontrado.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al buscar el cliente.', 500);
        }
    }
}
