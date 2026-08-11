<?php

namespace App\Http\Controllers\activity\payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\activity\ActivityMesRequest;
use App\Http\Requests\activity\ActivitySemanaRequest;
use App\Http\Requests\activity\BasquetPagoRequest;
use App\Http\Requests\activity\StoreBasquetPagoRequest;
use App\Service\activity\payment\BasquetPagoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BasquetPagoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BasquetPagoService $basquetPagoService
    ) {}

    public function index(BasquetPagoRequest $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->basquetPagoService->paginated((int) $perPage);

            return $this->successResponse($pagos, 'Listado de pagos de básquet.');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de básquet.', 500);
        }
    }

    public function showByMes(BasquetPagoRequest $request, string $mes): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $pagos = $this->basquetPagoService->filterByMes($mes, (int) $perPage);

            return $this->successResponse($pagos, "Pagos de básquet para el mes {$mes}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al filtrar los pagos de básquet.', 500);
        }
    }

    public function showByMonthYear(ActivityMesRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('mes', now()->month);
            $result = $this->basquetPagoService->filterByMonthYear($year, $month);

            return $this->successResponse([
                'registros' => $result['registros'],
                'mes' => $month,
                'año' => $year,
                'total_meses' => $result['total_meses'],
            ], "Pagos de básquet para {$year}-{$month}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de básquet por mes.', 500);
        }
    }

    public function showBySemana(ActivitySemanaRequest $request): JsonResponse
    {
        try {
            $year = (int) $request->input('year', now()->isoWeekYear());
            $week = (int) $request->input('semana', now()->isoWeek());
            $registros = $this->basquetPagoService->filterByWeek($year, $week);

            return $this->successResponse([
                'registros' => $registros,
                'semana' => $week,
                'año' => $year,
                'semana_actual' => now()->isoWeek(),
            ], "Pagos de básquet para la semana {$week} del año {$year}.");
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los pagos de básquet por semana.', 500);
        }
    }

    public function store(StoreBasquetPagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->basquetPagoService->create($request->validated());

            return $this->successResponse($pago, 'Pago de básquet registrado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
