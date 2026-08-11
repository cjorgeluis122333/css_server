<?php

namespace App\Http\Controllers\activity\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\StoreBattingClienteRequest;
use App\Http\Resources\activity\client\BattingClienteResource;
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

    public function store(StoreBattingClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->battingClienteService->create($request->validated());

            return $this->successResponse(new BattingClienteResource($cliente), 'Cliente de Batting registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function showByCedula(string $cedula): JsonResponse
    {
        try {
            $cliente = $this->battingClienteService->findByCedula($cedula);

            if (! $cliente) {
                return $this->errorResponse('No se encontró una coincidencia.', 404);
            }

            return $this->successResponse(new BattingClienteResource($cliente), 'Cliente encontrado.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al buscar el cliente.', 500);
        }
    }
}
