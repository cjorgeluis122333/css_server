<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoryPayRequest;
use App\Http\Resources\HistoryPayResource;
use App\Models\HistoryPay;
use App\Models\Partner;
use App\Service\HistoryPayService;
use App\Service\PartnerDebtService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistoryPayController extends Controller
{
    use ApiResponse;

    protected HistoryPayService $historyService;

    protected PartnerDebtService $debtService;

    public function __construct(HistoryPayService $historyService, PartnerDebtService $debtService)
    {
        $this->historyService = $historyService;
        $this->debtService = $debtService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 25);

            $query = HistoryPay::with('partner')
                ->orderBy('fecha', 'desc');

            // Eager load creator only for SUPER_ADMIN
            if ($request->user() && $request->user()->isSuperAdmin()) {
                $query->with('creator');
            }

            $history = $query->paginate($perPage);

            return HistoryPayResource::collection($history);

        } catch (Exception $e) {
            Log::error('Error al obtener historial de pagos: '.$e->getMessage());

            return response()->json([
                'error' => 'No se pudo recuperar el historial',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesa los pagos enviados desde el frontend.
     * Al usar un apiResource (POST /history), el "acc" viene en el body,
     * por lo que buscamos el modelo Partner manualmente.
     */
    public function store(HistoryPayRequest $request): JsonResponse
    {

        // 1. Validamos los datos (asegurándonos de que el 'acc' existe)
        $data = $request->validated();

        // 2. Buscamos el socio usando el 'acc' que viene en el JSON
        // Usamos firstOrFail() para que, si por alguna razón no existe, lance un 404
        $partner = Partner::where('acc', $data['acc'])->firstOrFail();

        try {
            // 3. Ejecutamos la lógica de pago
            // $request->except('pagos') incluirá el 'acc' y todos los demás metadatos
            $this->debtService->processPayments(
                $partner,
                $data['pagos'],
                $request->except('pagos')
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Los pagos se han procesado y escalado al valor nominal correctamente.',
            ], 201);

        } catch (\Exception $e) {
            // Si el monto supera la deuda o algo falla, retornamos el error
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Listar historial por socio con deuda acumulada en cada registro.
     */
    public function show(Request $request, $acc): JsonResponse
    {
        if ($request->user()->isPartner() && $request->user()->acc != $acc) {
            return $this->errorResponse('No tienes permiso para ver esta información.', 403);
        }

        try {
            // Calcula la deuda acumulada para cada registro (necesita todos los pagos)
            $deudaMap = $this->historyService->computeRunningDebtMap((int) $acc);

            $history = HistoryPay::where('acc', $acc)
                ->orderBy('fecha', 'desc')
                ->paginate(15);

            // Inyecta el campo deuda en cada registro de la página actual
            $history->getCollection()->transform(function (HistoryPay $record) use ($deudaMap) {
                $record->deuda = $deudaMap[(int) $record->ind] ?? null;

                return $record;
            });

            return response()->json($history);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener el historial de pagos.', 500);
        }
    }

    public function update(HistoryPayRequest $request, HistoryPay $historyPay)
    {
        $historyPay->update($request->validated());

        return $historyPay;
    }

    public function destroy(HistoryPay $historyPay)
    {
        $historyPay->delete();

        return response()->json();
    }
}
