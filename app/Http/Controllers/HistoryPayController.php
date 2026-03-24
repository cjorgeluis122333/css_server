<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoryPayRequest;
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
            // 1. Determinamos cuántos elementos por página (por defecto 15)
            $perPage = $request->input('per_page', 25);

            // 2. Construimos la consulta robusta
            $history = HistoryPay::with('partner') // Carga la relación para evitar lentitud
            ->orderBy('fecha', 'desc')         // Primero por fecha
            ->paginate($perPage);

            // 3. Retornamos la respuesta estructurada
            return response()->json($history, 200);

        } catch (Exception $e) {
            // Logueamos el error para debugging
            Log::error("Error al obtener historial de pagos: " . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo recuperar el historial',
                'details' => $e->getMessage()
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
                'status'  => 'success',
                'message' => 'Los pagos se han procesado y escalado al valor nominal correctamente.'
            ], 201);

        } catch (\Exception $e) {
            // Si el monto supera la deuda o algo falla, retornamos el error
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Listar historial por socio.
     */
    public function show(Request $request, $acc): JsonResponse
    {
        $history = HistoryPay::where('acc', $acc)
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        return response()->json($history);
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
