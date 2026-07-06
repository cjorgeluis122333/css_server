<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\BattingPagoRequest;
use App\Http\Requests\activity\StoreBattingPagoRequest;
use App\Service\activity\payment\BattingPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BattingPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BattingPagoService $battingPagoService
    ) {}

    public function index(BattingPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->battingPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de batting.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de batting.', 500);
        }
    }

    public function showByMes(BattingPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->battingPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de batting para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de batting.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->battingPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de batting para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de batting por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->battingPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de batting para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de batting por semana.', 500);
        }
    }

    public function store(StoreBattingPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->battingPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de batting registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
