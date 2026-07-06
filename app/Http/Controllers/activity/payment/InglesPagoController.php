<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\InglesPagoRequest;
use App\Http\Requests\activity\StoreInglesPagoRequest;
use App\Service\activity\payment\InglesPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InglesPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InglesPagoService $inglesPagoService
    ) {}

    public function index(InglesPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->inglesPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de inglés.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de inglés.', 500);
        }
    }

    public function showByMes(InglesPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->inglesPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de inglés para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de inglés.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->inglesPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de inglés para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de inglés por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->inglesPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de inglés para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de inglés por semana.', 500);
        }
    }

    public function store(StoreInglesPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->inglesPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de inglés registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
