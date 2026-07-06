<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreOnboxClienteRequest;
use App\Http\Resources\activity\client\OnboxClienteResource;
use App\Service\activity\client\OnboxClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OnboxClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OnboxClienteService $onboxClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->onboxClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Onbox.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Onbox.', 500);
        }
    }

    public function store(StoreOnboxClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->onboxClienteService->create($request->validated());

            return $this->successResponse(new OnboxClienteResource($cliente), 'Cliente de Onbox registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function showByCedula(string $cedula): JsonResponse
    {
        try {
            $cliente = $this->onboxClienteService->findByCedula($cedula);

            if (! $cliente) {
                return $this->errorResponse('No se encontró una coincidencia.', 404);
            }

            return $this->successResponse(new OnboxClienteResource($cliente), 'Cliente encontrado.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al buscar el cliente.', 500);
        }
    }
}
