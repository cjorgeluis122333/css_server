<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreInglesClienteRequest;
use App\Http\Resources\activity\client\InglesClienteResource;
use App\Service\activity\client\InglesClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InglesClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InglesClienteService $inglesClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->inglesClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Ingles.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Ingles.', 500);
        }
    }

    public function store(StoreInglesClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->inglesClienteService->create($request->validated());

            return $this->successResponse(new InglesClienteResource($cliente), 'Cliente de Ingles registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function showByCedula(string $cedula): JsonResponse
    {
        try {
            $cliente = $this->inglesClienteService->findByCedula($cedula);

            if (! $cliente) {
                return $this->errorResponse('No se encontró una coincidencia.', 404);
            }

            return $this->successResponse(new InglesClienteResource($cliente), 'Cliente encontrado.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al buscar el cliente.', 500);
        }
    }
}
