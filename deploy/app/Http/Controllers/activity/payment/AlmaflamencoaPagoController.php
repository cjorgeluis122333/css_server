<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\AlmaflamencoaPagoRequest;
use App\Http\Requests\activity\StoreAlmaflamencoaPagoRequest;
use App\Service\activity\payment\AlmaflamencoaPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AlmaflamencoaPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AlmaflamencoaPagoService $almaflamencoaPagoService
    ) {}

    public function index(AlmaflamencoaPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->almaflamencoaPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Alma Flamenca.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Alma Flamenca.', 500);
        }
    }

    public function showByMes(AlmaflamencoaPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->almaflamencoaPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Alma Flamenca para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Alma Flamenca.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->almaflamencoaPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de alma flamenca para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de alma flamenca por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->almaflamencoaPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de alma flamenca para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de alma flamenca por semana.', 500);
        }
    }

    public function store(StoreAlmaflamencoaPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->almaflamencoaPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Alma Flamenca registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
