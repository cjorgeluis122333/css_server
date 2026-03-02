<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoryPayRequest;
use App\Models\HistoryPay;
use App\Service\HistoryPayService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistoryPayController extends Controller
{

    use ApiResponse;

    protected HistoryPayService $historyService;

    public function __construct(HistoryPayService $historyService)
    {
        $this->historyService = $historyService;
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

        } catch (\Exception $e) {
            // Logueamos el error para debugging
            Log::error("Error al obtener historial de pagos: " . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo recuperar el historial',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacenar un nuevo historial.
     */
    public function store(HistoryPayRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $history = $this->historyService->createHistory($data);

            return $this->successResponse(
                $history,
                'Historial de pago registrado correctamente',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'No se pudo registrar el pago: ' . $e->getMessage(),
                500
            );
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
