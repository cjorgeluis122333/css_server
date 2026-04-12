<?php

namespace App\Http\Controllers;
use App\Exports\TitularDebtExport;
use App\Service\PartnerDebtService;
use App\Service\PartnerService;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    protected PartnerDebtService $debtService;
    // Inyectamos el servicio en el constructor
    public function __construct(PartnerDebtService $debtService)
    {
        $this->debtService = $debtService;
    }


    public function exportTitularDebtSummaryByYearInExel(int $year)
    {
        try {
            // Reutilizamos tu servicio actual
            $summary = $this->debtService->titularDebtSummaryByYear($year);

            if (empty($summary)) {
                return response()->json(['message' => 'No hay datos para exportar'], 404);
            }

            $fileName = "Solvencia_Titulares_{$year}.xlsx";

            // Retornamos la descarga directa
            return Excel::download(new TitularDebtExport($summary, $year), $fileName);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
