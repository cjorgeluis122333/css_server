<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\StoreStrongPagoRequest;
use App\Http\Requests\activity\StrongPagoRequest;
use App\Service\activity\payment\StrongPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class StrongPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StrongPagoService $strongPagoService
    ) {}

    public function index(StrongPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->strongPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Strong.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Strong.', 500);
        }
    }

    public function showByMes(StrongPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->strongPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Strong para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Strong.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->strongPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de strong para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de strong por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->strongPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de strong para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de strong por semana.', 500);
        }
    }

    public function store(StoreStrongPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->strongPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Strong registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
