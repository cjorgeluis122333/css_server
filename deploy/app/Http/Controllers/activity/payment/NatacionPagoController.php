<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\NatacionPagoRequest;
use App\Http\Requests\activity\StoreNatacionPagoRequest;
use App\Service\activity\payment\NatacionPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NatacionPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NatacionPagoService $natacionPagoService
    ) {}

    public function index(NatacionPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->natacionPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de natación.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de natación.', 500);
        }
    }

    public function showByMes(NatacionPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->natacionPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de natación para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de natación.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->natacionPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de natación para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de natación por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->natacionPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de natación para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de natación por semana.', 500);
        }
    }

    public function store(StoreNatacionPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->natacionPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de natación registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
