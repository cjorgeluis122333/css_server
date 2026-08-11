<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\KaratePagoRequest;
use App\Http\Requests\activity\StoreKaratePagoRequest;
use App\Service\activity\payment\KaratePagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class KaratePagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected KaratePagoService $karatePagoService
    ) {}

    public function index(KaratePagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->karatePagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de karate.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de karate.', 500);
        }
    }

    public function showByMes(KaratePagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->karatePagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de karate para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de karate.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->karatePagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de karate para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de karate por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->karatePagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de karate para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de karate por semana.', 500);
        }
    }

    public function store(StoreKaratePagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->karatePagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de karate registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
