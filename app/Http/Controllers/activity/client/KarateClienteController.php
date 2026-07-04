<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreKarateClienteRequest;
use App\Http\Resources\activity\client\KarateClienteResource;
use App\Service\activity\client\KarateClienteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class KarateClienteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected KarateClienteService $karateClienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->karateClienteService->getAll();

            return $this->successResponse($clientes, 'Listado de clientes de Karate.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los clientes de Karate.', 500);
        }
    }

    public function store(StoreKarateClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->karateClienteService->create($request->validated());

            return $this->successResponse(new KarateClienteResource($cliente), 'Cliente de Karate registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
