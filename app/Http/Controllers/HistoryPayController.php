<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoryPayRequest;
use App\Models\HistoryPay;
use App\Service\HistoryPayService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class HistoryPayController extends Controller
{

    use ApiResponse;

    protected HistoryPayService $historyService;

    public function __construct(HistoryPayService $historyService)
    {
        $this->historyService = $historyService;
    }
    public function index()
    {
        return HistoryPay::all();
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
        } catch (\Exception $e) {
            return $this->errorResponse(
                'No se pudo registrar el pago: ' . $e->getMessage(),
                500
            );
        }
    }
    /**
     * Listar historial por socio.
     */
    public function show($acc): JsonResponse
    {
        $history = $this->historyService->getHistoryByAccount($acc);

        return $this->successResponse($history, "Historial de la cuenta: $acc");
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
