<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreAlmaflamencaClienteRequest;
use App\Http\Resources\activity\client\AlmaflamencaClienteResource;
use App\Service\activity\client\AlmaflamencaClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AlmaflamencaClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AlmaflamencaClienteService $almaflamencaClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->almaflamencaClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Alma Flamenca.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Alma Flamenca.', 500);
        }
    }

    public function store(StoreAlmaflamencaClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->almaflamencaClienteService->create($request->validated());

            return $this->successResponse(new AlmaflamencaClienteResource($cliente), 'Cliente de Alma Flamenca registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
