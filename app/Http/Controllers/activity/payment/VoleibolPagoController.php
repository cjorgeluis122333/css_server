<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\StoreVoleibolPagoRequest;
use App\Http\Requests\activity\VoleibolPagoRequest;
use App\Service\activity\payment\VoleibolPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VoleibolPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VoleibolPagoService $voleibolPagoService
    ) {}

    public function index(VoleibolPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->voleibolPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de voleibol.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de voleibol.', 500);
        }
    }

    public function showByMes(VoleibolPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->voleibolPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de voleibol para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de voleibol.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->voleibolPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de voleibol para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de voleibol por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->voleibolPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de voleibol para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de voleibol por semana.', 500);
        }
    }

    public function store(StoreVoleibolPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->voleibolPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de voleibol registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
