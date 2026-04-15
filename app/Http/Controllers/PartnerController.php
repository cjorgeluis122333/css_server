<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartnerRequest;
use App\Models\Partner;
use App\Service\PartnerDebtService;
use App\Service\PartnerService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PartnerController extends Controller
{
    use ApiResponse;

    protected PartnerService $partnerService;

    protected PartnerDebtService $debtService;

    /**
     * Campos ligeros para el listado masivo (Index).
     */
    protected array $selectIndex = [
        'ind', 'acc', 'nombre', 'cedula', 'carnet', 'celular', 'correo', 'nacimiento', 'categoria', 'telefono',  'ingreso', 'direccion', 'ocupacion',  'cobrador',
    ];

    // Inyectamos el servicio en el constructor
    public function __construct(PartnerService $partnerService, PartnerDebtService $debtService)
    {
        $this->partnerService = $partnerService;
        $this->debtService = $debtService;
    }

    /**
     * /partners/debs/5?adelanto=3
     * Muestra el estado de cuenta.
     * Si no se envía el parámetro 'adelanto', funciona exactamente como antes.
     */
    public function showDebts(int $id): JsonResponse
    {
        // 1. Buscamos al socio
        $partner = Partner::findOrFail($id);

        $mesesAdelanto = (int) request()->query('adelanto', 0);

        try {
            $limitDate = $mesesAdelanto > 0
                ? now()->addMonths($mesesAdelanto)->format('Y-m')
                : null;

            // Aquí $result ahora es un array
            $result = $this->debtService->getAccountStatement($partner, $limitDate);

            return response()->json([
                'message' => 'success',
                'data' => [
                    'socio' => [
                        'nombre' => $partner->nombre,
                        'acc' => $partner->acc,
                        'categoria' => $partner->categoria,
                    ],
                    // Accedemos a las llaves del array
                    'hijos_mayores_30' => $result['hijos_mayores'],
                    'resumen_deudas' => $result['debts'],
                    'total_a_pagar' => round($result['debts']->sum('deuda_pendiente'), 2),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'No se pudo calcular la deuda: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera una cotización de pagos por adelantado para un socio.
     *
     * * @param int $id ID del socio
     */
    public function getAdvanceQuotes(int $id): JsonResponse
    {
        // 1. Buscamos al socio
        $partner = Partner::findOrFail($id);

        // 2. Obtenemos cuántos meses quiere proyectar (por defecto 12)
        $monthsToProject = (int) request()->query('adelanto', 12);

        try {
            // Llamamos al servicio que ahora nos devuelve un array con todo
            $result = $this->debtService->getAdvancePaymentsQuotes($partner, $monthsToProject);

            $quotes = $result['quotes'];
            $hijos = $result['hijos_mayores'];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'socio' => [
                        'nombre' => $partner->nombre,
                        'acc' => $partner->acc,
                        'categoria' => $partner->categoria,
                        'hijos_registrados' => $hijos, // <-- Aquí incluimos los nombres
                    ],
                    'resumen_deudas' => $quotes,
                    'total_a_pagar' => round($quotes->sum('deuda_pendiente'), 2),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar cotización: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return JsonResponse
     * SOLVENCIA
     */
    public function titularDebtSummary(): JsonResponse
    {
        try {
            $summary = $this->debtService->getTitularDebtSummaryList();

            return response()->json([
                'status' => 'success',
                'count' => count($summary),
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Error al generar la deuda de titulares: '.$e->getMessage(), 500);
        }
    }
    public function titularDebtSummaryByYear(int $year): JsonResponse
    {
        try {
            // Recibe solo el año por parámetro (ej. 2026) y lo pasa al servicio
            $summary = $this->debtService->titularDebtSummaryByYear($year);

            return response()->json([
                'status' => 'success',
                'count' => count($summary),
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Error al generar la deuda de titulares: '.$e->getMessage(), 500);
        }
    }
    /**
     * @return JsonResponse
     * Access
     */
    public function access_controller(): JsonResponse
    {
        try {
            $partners = $this->partnerService->getValidPartnersForAccess();

            return response()->json([
                'status' => 'susses',
                'count' => $partners->count(),
                'data' => $partners,
            ], 200);

        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la lista de acceso: '.$e->getMessage(), 500);
        }
    }
    /**
     * Retorna las métricas globales de morosidad y deudas del club.
     *
     * @return JsonResponse
     * MÉTRICAS DE DEUDA GLOBAL
     */
    public function globalDebtMetrics(): JsonResponse
    {
        try {
            // Invocamos el método que extrae los totales, morosos de 3 y 6 meses
            $metrics = $this->debtService->getGlobalDebtMetrics();

            return response()->json([
                'status' => 'success',
                'data'   => $metrics,
            ]);
        } catch (Exception $e) {
            // Mantenemos tu estándar de manejo de errores
            return $this->errorResponse('Error al generar las métricas globales de deuda: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ejecuta el servicio para obtener el conteo de invitados del mes por acción.
     * Get: /partner/guest
     */
    public function getMonthlyGuestsCount(): JsonResponse
    {
        try {
            $data = $this->partnerService->getGuestCountThisMonth();

            return response()->json([
                'success' => true,
                'data'    => $data
            ], 200);

        } catch (Exception $e) {
            // Manejo básico de errores para no exponer la traza completa
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la consulta.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/partners
     * Parameters:  (page, per_page)
     */
    public function index(Request $request)
    {
        // Seleccionamos solo lo necesario directamente en la consulta
        $partners = Partner::holders()
            ->select($this->selectIndex)
            ->orderBy('acc', 'asc')
            ->paginate($request->input('per_page', 50));

        return response()->json($partners);
    }

    /**
     * GET /api/partners/{id}
     * Muestra el detalle completo de un Titular.
     */
    public function show($id)
    {
        // Buscamos por ID ('ind') pero aseguramos que sea TITULAR.
        // Si el ID existe pero es un FAMILIAR, devolverá 404 (seguridad).
        $partner = Partner::holders()
            ->where('acc', $id)
            ->first();

        if (! $partner) {
            return $this->errorResponse('Socio titular no encontrado', 404);
        }

        return $this->successResponse($partner);
    }

    /**
     * POST /api/partners
     * Crea un nuevo Socio Titular.
     */
    public function store(PartnerRequest $request)
    {
        try {
            // Solo enviamos los datos ya validados por el PartnerRequest
            $partner = $this->partnerService->createTitular($request->validated());

            return $this->successResponse(
                $partner,
                'Socio titular creado exitosamente',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('Error al crear socio: '.$e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/partners/{acc}
     * Actualiza usando la ACC como identificador en la URL.
     */
    public function update(PartnerRequest $request, $acc)
    {
        // 1. Buscamos al socio por su ACC y aseguramos que sea TITULAR.
        // Si no existe, firstOrFail lanza 404 automáticamente (o puedes usar if tradicional).
        try {
            $partner = Partner::holders()->where('acc', $acc)->firstOrFail();

            // 2. Delegamos la actualización al servicio
            $updatedPartner = $this->partnerService->updateTitular(
                $partner,
                $request->validated()
            );

            return $this->successResponse($updatedPartner, 'Socio titular actualizado con éxito');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Socio titular no encontrado con esa Acción', 404);
        } catch (Exception $e) {
            Log::error("Error update partner {$acc}: ".$e->getMessage());

            return $this->errorResponse('No se pudo actualizar el socio', 500);
        }
    }

    /**
     * DELETE /api/partners/{id}
     * Elimina un socio (Hard Delete).
     */
    public function destroy($acc)
    {
        try {
            // Buscamos por ACC antes de intentar borrar
            $partner = Partner::holders()->where('acc', $acc)->firstOrFail();

            $this->partnerService->deleteTitular($partner);

            return $this->successResponse(null, 'Socio eliminado permanentemente');

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Socio no encontrado', 404);
        } catch (Exception $e) {
            // Capturamos la excepción de negocio (ej: tiene familiares)
            // Asumimos que el código 409 es para conflicto de lógica
            return $this->errorResponse($e->getMessage(), 409);
        }
    }
}
