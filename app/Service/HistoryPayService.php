<?php

namespace App\Service;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\HistoryPay;

class HistoryPayService
{
    /**
     * Crea un nuevo registro de historial.
     */
    public function createHistory(array $data): HistoryPay
    {
        try {
            $data['performed_by'] = auth()->id();
            return HistoryPay::create($data);
        } catch (Exception $e) {
            Log::error("Error al crear historial de pago: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener el historial de un socio específico por su cuenta (acc)
     */
    public function getHistoryByAccount(int $acc)
    {
        return HistoryPay::where('acc', $acc)->orderBy('ind', 'desc')->get();
    }
}
