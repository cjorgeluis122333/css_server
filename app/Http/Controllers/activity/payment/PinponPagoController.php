<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\PinponPagoRequest;
use App\Http\Requests\activity\StorePinponPagoRequest;
use App\Service\activity\payment\PinponPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PinponPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PinponPagoService $pinponPagoService
    ) {}

    public function index(PinponPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->pinponPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de Pin Pon.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de Pin Pon.', 500);
        }
    }

    public function showByMes(PinponPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->pinponPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de Pin Pon para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de Pin Pon.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->pinponPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de pin pon para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de pin pon por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->pinponPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de pin pon para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de pin pon por semana.', 500);
        }
    }

    public function store(StorePinponPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->pinponPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de Pin Pon registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
