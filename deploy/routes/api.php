<?php

use App\Http\Controllers\activity\client\AlmaflamencaClienteController;
use App\Http\Controllers\activity\client\BasquetClienteController;
use App\Http\Controllers\activity\client\BattingClienteController;
use App\Http\Controllers\activity\client\InglesClienteController;
use App\Http\Controllers\activity\client\KarateClienteController;
use App\Http\Controllers\activity\client\LeverClienteController;
use App\Http\Controllers\activity\client\NatacionClienteController;
use App\Http\Controllers\activity\client\OnboxClienteController;
use App\Http\Controllers\activity\client\PinponClienteController;
use App\Http\Controllers\activity\client\StrongClienteController;
use App\Http\Controllers\activity\client\VoleibolClienteController;
use App\Http\Controllers\activity\payment\AlmaflamencoaPagoController;
use App\Http\Controllers\activity\payment\BasquetPagoController;
use App\Http\Controllers\activity\payment\BattingPagoController;
use App\Http\Controllers\activity\payment\InglesPagoController;
use App\Http\Controllers\activity\payment\KaratePagoController;
use App\Http\Controllers\activity\payment\LeverPagoController;
use App\Http\Controllers\activity\payment\NatacionPagoController;
use App\Http\Controllers\activity\payment\OnboxPagoController;
use App\Http\Controllers\activity\payment\PinponPagoController;
use App\Http\Controllers\activity\payment\StrongPagoController;
use App\Http\Controllers\activity\payment\VoleibolPagoController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\auth\PasswordResetController;
use App\Http\Controllers\auth\UserAdminController;
use App\Http\Controllers\exel\ExcelController;
use App\Http\Controllers\partners\FamilyController;
use App\Http\Controllers\partners\FeeController;
use App\Http\Controllers\partners\GuestController;
use App\Http\Controllers\partners\HallControlController;
use App\Http\Controllers\partners\HallController;
use App\Http\Controllers\partners\HistoryPayController;
use App\Http\Controllers\partners\ManagerBoardsController;
use App\Http\Controllers\partners\ManagerController;
use App\Http\Controllers\partners\PartnerController;
use App\Http\Controllers\partners\RegisteredGuestController;
use App\Http\Controllers\photo\PartnerPhotoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// RUTA TEMPORAL DE VERIFICACIÓN DE DESPLIEGUE
// ⚠️  ELIMINAR ESTA RUTA DESPUÉS DE CONFIRMAR QUE EL DEPLOY FUNCIONA ⚠️
// Acceso: GET /api/deploy-check/{secret}
// El {secret} debe coincidir con DEPLOY_CHECK_SECRET en .env
// ---------------------------------------------------------------------------
Route::get('/deploy-check/{secret}', function (string $secret) {
    if ($secret !== config('app.deploy_check_secret')) {
        abort(403);
    }

    $results = [];

    // 1. Verificar entorno
    $results['app_env'] = config('app.env');
    $results['app_debug'] = config('app.debug');
    $results['app_url'] = config('app.url');
    $results['php_version'] = PHP_VERSION;

    // 2. Verificar conexión a base de datos
    try {
        DB::connection()->getPdo();
        $dbVersion = DB::selectOne('SELECT VERSION() as version');
        $results['database'] = [
            'status' => 'connected',
            'driver' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'version' => $dbVersion->version ?? 'unknown',
        ];
    } catch (Exception $e) {
        $results['database'] = [
            'status' => 'ERROR',
            'message' => $e->getMessage(),
        ];
    }

    // 3. Verificar extensiones PHP críticas
    $requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl', 'zip', 'bcmath', 'gd', 'curl'];
    foreach ($requiredExtensions as $ext) {
        $results['extensions'][$ext] = extension_loaded($ext) ? 'OK' : 'MISSING';
    }

    // 4. Verificar permisos de escritura en storage/
    $writablePaths = [
        storage_path('logs'),
        storage_path('framework/cache'),
        storage_path('framework/sessions'),
        storage_path('framework/views'),
        base_path('bootstrap/cache'),
    ];
    foreach ($writablePaths as $path) {
        $results['writable'][basename(dirname($path)).'/'.basename($path)] = is_writable($path) ? 'OK' : 'NOT WRITABLE';
    }

    return response()->json([
        'status' => 'ok',
        'message' => '⚠️ Elimina esta ruta después de verificar el deploy.',
        'data' => $results,
    ]);
})->name('deploy-check');
// ---------------------------------------------------------------------------

// --- Public routes ---
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/partners/solvencia', [PartnerController::class, 'titularDebtSummary'])->name('partners.titularDebtSummary');
Route::get('/partners/access', [PartnerController::class, 'access_controller'])->name('partners.access');

// --- Password recovery (public, no auth required) ---
Route::post('/forgot-password/request', [PasswordResetController::class, 'request'])->name('forgot-password.request');
Route::post('/forgot-password/verify', [PasswordResetController::class, 'verify'])->name('forgot-password.verify');
Route::post('/forgot-password/reset', [PasswordResetController::class, 'reset'])->name('forgot-password.reset');

// --- Password recovery without external service (acc + cedula + correo) ---
Route::post('/forgot-password/direct/validate', [PasswordResetController::class, 'directValidate'])->name('forgot-password.direct.validate');
Route::post('/forgot-password/direct/reset', [PasswordResetController::class, 'directReset'])->name('forgot-password.direct.reset');

// --- Authenticated routes ---
Route::middleware('auth:sanctum')->group(function () {

    // === Open to all authenticated users ===
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/halls-pay', [HallController::class, 'index'])->name('halls-pay.index');
    Route::get('/halls-pay/{halls_pay}', [HallController::class, 'show'])->name('halls-pay.show');
    Route::get('/partners/photo/{cedula}', [PartnerPhotoController::class, 'show'])->name('partners.photo');
    Route::post('/partners/photo/{acc}', [PartnerPhotoController::class, 'store'])->name('partners.photo.store');

    // === Solvencia propia (PARTNER ve su deuda, HONORARY exonerado) ===
    Route::middleware('can:view-own-debt')->group(function () {
        Route::get('/partners/debs/{id}', [PartnerController::class, 'showDebts'])->name('showDebts');
        Route::get('/partners/debs/advance/{id}', [PartnerController::class, 'getAdvanceQuotes'])->name('partners.advanceQuotes');
        Route::get('/history/{history}', [HistoryPayController::class, 'show'])->name('history.show');
        Route::get('/history/{acc}/until/{mes}', [HistoryPayController::class, 'showPaymentsUntilMonth'])
            ->where('mes', '\d{4}-\d{2}')
            ->name('history.byMonth');
    });

    // === Finanzas: pagos, historial, Excel, métricas (SUPER_ADMIN + ADMIN) ===
    Route::middleware('can:access-finanzas')->group(function () {
        Route::get('/history', [HistoryPayController::class, 'index'])->name('history.index');
        Route::post('/history', [HistoryPayController::class, 'store'])->name('history.store');
        Route::put('/history/{history}', [HistoryPayController::class, 'update'])->name('history.update');
        Route::delete('/history/{history}', [HistoryPayController::class, 'destroy'])->name('history.destroy');
        Route::get('/partners/solvencia/metrics', [PartnerController::class, 'globalDebtMetrics'])->name('partners.globalDebtMetrics');
        Route::get('/partners/solvencia/metrics/{metric}', [PartnerController::class, 'partnersByDebtMetric'])->name('partners.partnersByDebtMetric');
        Route::get('/generate/exel/solvencia/{year}', [ExcelController::class, 'exportTitularDebtSummaryByYearInExel'])->name('exel.solvencia');
    });

    // === Solvencia: vista global por año (SUPER_ADMIN + ADMIN + OPERATOR + SUPERVISOR) ===
    Route::middleware('can:access-solvencia')->group(function () {
        Route::get('/partners/solvencia/{year}', [PartnerController::class, 'titularDebtSummaryByYear'])->name('partners.titularDebtSummaryByYear');
    });

    // === Cuotas: gestión de cuotas (solo SUPER_ADMIN) ===
    Route::middleware('can:manage-cuotas')->group(function () {
        Route::get('/fee/showByMonth/{mes?}', [FeeController::class, 'showByMonth'])->name('showByMonth');
        Route::apiResource('fee', FeeController::class);
    });

    // === Socios: listar todos (SUPER_ADMIN + ADMIN) ===
    Route::middleware('can:list-socios')->group(function () {
        Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
    });

    // === Socios: ver individual (SUPER_ADMIN + ADMIN + OPERATOR + HONORARY own + PARTNER own) ===
    Route::middleware('can:view-socios')->group(function () {
        Route::get('/partners/{partner}', [PartnerController::class, 'show'])->name('partners.show');
        Route::get('/family/{family}', [FamilyController::class, 'show'])->name('family.show');
    });

    // === Socios: crear, editar, eliminar (SUPER_ADMIN + ADMIN + OPERATOR) ===
    Route::middleware('can:manage-socios')->group(function () {
        Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');
        Route::put('/partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
        Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');
        Route::get('/family', [FamilyController::class, 'index'])->name('family.index');
        Route::post('/family', [FamilyController::class, 'store'])->name('family.store');
        Route::put('/family/{family}', [FamilyController::class, 'update'])->name('family.update');
        Route::delete('/family/{family}', [FamilyController::class, 'destroy'])->name('family.destroy');
    });

    // === Directivos: manager y boards (SUPER_ADMIN + ADMIN) ===
    Route::middleware('can:manage-directivos')->group(function () {
        Route::apiResource('/manager', ManagerController::class);
        Route::apiResource('/board', ManagerBoardsController::class);
    });

    // === Salones: ver todos los registros de control ===
    Route::middleware('can:view-salones')->group(function () {
        Route::get('/halls-control', [HallControlController::class, 'index'])->name('halls-control.index');
        Route::get('/halls-control/recent', [HallControlController::class, 'recentHistory']);
        Route::get('/halls-control/{halls_control}', [HallControlController::class, 'show'])->name('halls-control.show');
    });

    // === Salones: reservar/eliminar (SUPER_ADMIN + ADMIN + HONORARY own + PARTNER own) ===
    Route::middleware('can:reserve-salones')->group(function () {
        Route::post('/halls-control', [HallControlController::class, 'store'])->name('halls-control.store');
        Route::delete('/halls-control/{halls_control}', [HallControlController::class, 'destroy'])->name('halls-control.destroy');
    });

    // === Salones: editar reservas (SUPER_ADMIN + ADMIN únicamente) ===
    Route::middleware('can:manage-halls-control')->group(function () {
        Route::put('/halls-control/{halls_control}', [HallControlController::class, 'update'])->name('halls-control.update');
    });

    // === Salones: gestión de precios (SUPER_ADMIN + ADMIN) ===
    Route::middleware('can:manage-salones-precios')->group(function () {
        Route::post('/halls-pay', [HallController::class, 'store'])->name('halls-pay.store');
        Route::put('/halls-pay/{halls_pay}', [HallController::class, 'update'])->name('halls-pay.update');
        Route::delete('/halls-pay/{halls_pay}', [HallController::class, 'destroy'])->name('halls-pay.destroy');
    });

    // === Invitados (SUPER_ADMIN + ADMIN + OPERATOR + SUPERVISOR + HONORARY own + PARTNER own) ===
    Route::middleware('can:access-invitados')->group(function () {
        Route::get('/guest/{acc}', [GuestController::class, 'index'])->name('guest.index');
        Route::get('/guest-current/{acc}', [GuestController::class, 'currentMonth'])->name('guest.current-month');
        Route::get('/guests-all', [GuestController::class, 'allGuests'])->name('guest.all');
        Route::apiResource('/guest', GuestController::class);
        Route::get('/partner/guest', [PartnerController::class, 'getMonthlyGuestsCount'])->name('guest.guest-count');
        Route::apiResource('/register-guest', RegisteredGuestController::class);
    });

    // === Administración de usuarios (SUPER_ADMIN + ADMIN) ===
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/user-admin', [UserAdminController::class, 'index']);
        Route::put('/user-admin/{acc}', [UserAdminController::class, 'update']);
        Route::delete('/user-admin/{acc}', [UserAdminController::class, 'destroy']);
    });

    // === Actividades: pagos (todos los usuarios autenticados) ===
    Route::prefix('activity')->group(function () {
        Route::get('/natacion', [NatacionPagoController::class, 'index'])->name('activity.natacion.index');
        Route::get('/natacion/mes', [NatacionPagoController::class, 'showByMonthYear'])->name('activity.natacion.showByMonthYear');
        Route::get('/natacion/semana', [NatacionPagoController::class, 'showBySemana'])->name('activity.natacion.showBySemana');
        Route::get('/natacion/{mes}', [NatacionPagoController::class, 'showByMes'])->name('activity.natacion.showByMes');
        Route::get('/onbox', [OnboxPagoController::class, 'index'])->name('activity.onbox.index');
        Route::get('/onbox/mes', [OnboxPagoController::class, 'showByMonthYear'])->name('activity.onbox.showByMonthYear');
        Route::get('/onbox/semana', [OnboxPagoController::class, 'showBySemana'])->name('activity.onbox.showBySemana');
        Route::get('/onbox/{mes}', [OnboxPagoController::class, 'showByMes'])->name('activity.onbox.showByMes');
        Route::get('/lever', [LeverPagoController::class, 'index'])->name('activity.lever.index');
        Route::get('/lever/mes', [LeverPagoController::class, 'showByMonthYear'])->name('activity.lever.showByMonthYear');
        Route::get('/lever/semana', [LeverPagoController::class, 'showBySemana'])->name('activity.lever.showBySemana');
        Route::get('/lever/{mes}', [LeverPagoController::class, 'showByMes'])->name('activity.lever.showByMes');
        Route::get('/pinpon', [PinponPagoController::class, 'index'])->name('activity.pinpon.index');
        Route::get('/pinpon/mes', [PinponPagoController::class, 'showByMonthYear'])->name('activity.pinpon.showByMonthYear');
        Route::get('/pinpon/semana', [PinponPagoController::class, 'showBySemana'])->name('activity.pinpon.showBySemana');
        Route::get('/pinpon/{mes}', [PinponPagoController::class, 'showByMes'])->name('activity.pinpon.showByMes');
        Route::get('/basquet', [BasquetPagoController::class, 'index'])->name('activity.basquet.index');
        Route::get('/basquet/mes', [BasquetPagoController::class, 'showByMonthYear'])->name('activity.basquet.showByMonthYear');
        Route::get('/basquet/semana', [BasquetPagoController::class, 'showBySemana'])->name('activity.basquet.showBySemana');
        Route::get('/basquet/{mes}', [BasquetPagoController::class, 'showByMes'])->name('activity.basquet.showByMes');
        Route::get('/strong', [StrongPagoController::class, 'index'])->name('activity.strong.index');
        Route::get('/strong/mes', [StrongPagoController::class, 'showByMonthYear'])->name('activity.strong.showByMonthYear');
        Route::get('/strong/semana', [StrongPagoController::class, 'showBySemana'])->name('activity.strong.showBySemana');
        Route::get('/strong/{mes}', [StrongPagoController::class, 'showByMes'])->name('activity.strong.showByMes');
        Route::get('/karate', [KaratePagoController::class, 'index'])->name('activity.karate.index');
        Route::get('/karate/mes', [KaratePagoController::class, 'showByMonthYear'])->name('activity.karate.showByMonthYear');
        Route::get('/karate/semana', [KaratePagoController::class, 'showBySemana'])->name('activity.karate.showBySemana');
        Route::get('/karate/{mes}', [KaratePagoController::class, 'showByMes'])->name('activity.karate.showByMes');
        Route::get('/ingles', [InglesPagoController::class, 'index'])->name('activity.ingles.index');
        Route::get('/ingles/mes', [InglesPagoController::class, 'showByMonthYear'])->name('activity.ingles.showByMonthYear');
        Route::get('/ingles/semana', [InglesPagoController::class, 'showBySemana'])->name('activity.ingles.showBySemana');
        Route::get('/ingles/{mes}', [InglesPagoController::class, 'showByMes'])->name('activity.ingles.showByMes');
        Route::get('/voleibol', [VoleibolPagoController::class, 'index'])->name('activity.voleibol.index');
        Route::get('/voleibol/mes', [VoleibolPagoController::class, 'showByMonthYear'])->name('activity.voleibol.showByMonthYear');
        Route::get('/voleibol/semana', [VoleibolPagoController::class, 'showBySemana'])->name('activity.voleibol.showBySemana');
        Route::get('/voleibol/{mes}', [VoleibolPagoController::class, 'showByMes'])->name('activity.voleibol.showByMes');
        Route::get('/batting', [BattingPagoController::class, 'index'])->name('activity.batting.index');
        Route::get('/batting/mes', [BattingPagoController::class, 'showByMonthYear'])->name('activity.batting.showByMonthYear');
        Route::get('/batting/semana', [BattingPagoController::class, 'showBySemana'])->name('activity.batting.showBySemana');
        Route::get('/batting/{mes}', [BattingPagoController::class, 'showByMes'])->name('activity.batting.showByMes');
        Route::get('/almaflamenca', [AlmaflamencoaPagoController::class, 'index'])->name('activity.almaflamenca.index');
        Route::get('/almaflamenca/mes', [AlmaflamencoaPagoController::class, 'showByMonthYear'])->name('activity.almaflamenca.showByMonthYear');
        Route::get('/almaflamenca/semana', [AlmaflamencoaPagoController::class, 'showBySemana'])->name('activity.almaflamenca.showBySemana');
        Route::get('/almaflamenca/{mes}', [AlmaflamencoaPagoController::class, 'showByMes'])->name('activity.almaflamenca.showByMes');
    });

    // === Actividades: clientes (todos los usuarios autenticados) ===
    Route::prefix('activity/client')->group(function () {
        Route::get('/natacion', [NatacionClienteController::class, 'index'])->name('activity.client.natacion.index');
        Route::get('/natacion/{cedula}', [NatacionClienteController::class, 'showByCedula'])->name('activity.client.natacion.showByCedula');
        Route::get('/onbox', [OnboxClienteController::class, 'index'])->name('activity.client.onbox.index');
        Route::get('/onbox/{cedula}', [OnboxClienteController::class, 'showByCedula'])->name('activity.client.onbox.showByCedula');
        Route::get('/lever', [LeverClienteController::class, 'index'])->name('activity.client.lever.index');
        Route::get('/lever/{cedula}', [LeverClienteController::class, 'showByCedula'])->name('activity.client.lever.showByCedula');
        Route::get('/pinpon', [PinponClienteController::class, 'index'])->name('activity.client.pinpon.index');
        Route::get('/pinpon/{cedula}', [PinponClienteController::class, 'showByCedula'])->name('activity.client.pinpon.showByCedula');
        Route::get('/basquet', [BasquetClienteController::class, 'index'])->name('activity.client.basquet.index');
        Route::get('/basquet/{cedula}', [BasquetClienteController::class, 'showByCedula'])->name('activity.client.basquet.showByCedula');
        Route::get('/strong', [StrongClienteController::class, 'index'])->name('activity.client.strong.index');
        Route::get('/strong/{cedula}', [StrongClienteController::class, 'showByCedula'])->name('activity.client.strong.showByCedula');
        Route::get('/karate', [KarateClienteController::class, 'index'])->name('activity.client.karate.index');
        Route::get('/karate/{cedula}', [KarateClienteController::class, 'showByCedula'])->name('activity.client.karate.showByCedula');
        Route::get('/ingles', [InglesClienteController::class, 'index'])->name('activity.client.ingles.index');
        Route::get('/ingles/{cedula}', [InglesClienteController::class, 'showByCedula'])->name('activity.client.ingles.showByCedula');
        Route::get('/voleibol', [VoleibolClienteController::class, 'index'])->name('activity.client.voleibol.index');
        Route::get('/voleibol/{cedula}', [VoleibolClienteController::class, 'showByCedula'])->name('activity.client.voleibol.showByCedula');
        Route::get('/batting', [BattingClienteController::class, 'index'])->name('activity.client.batting.index');
        Route::get('/batting/{cedula}', [BattingClienteController::class, 'showByCedula'])->name('activity.client.batting.showByCedula');
        Route::get('/almaflamenca', [AlmaflamencaClienteController::class, 'index'])->name('activity.client.almaflamenca.index');
        Route::get('/almaflamenca/{cedula}', [AlmaflamencaClienteController::class, 'showByCedula'])->name('activity.client.almaflamenca.showByCedula');
    });

    // === Actividades: registro de clientes (access-finanzas) ===
    Route::middleware('can:access-finanzas')->prefix('activity/client')->group(function () {
        Route::post('/natacion', [NatacionClienteController::class, 'store'])->name('activity.client.natacion.store');
        Route::post('/onbox', [OnboxClienteController::class, 'store'])->name('activity.client.onbox.store');
        Route::post('/lever', [LeverClienteController::class, 'store'])->name('activity.client.lever.store');
        Route::post('/pinpon', [PinponClienteController::class, 'store'])->name('activity.client.pinpon.store');
        Route::post('/basquet', [BasquetClienteController::class, 'store'])->name('activity.client.basquet.store');
        Route::post('/strong', [StrongClienteController::class, 'store'])->name('activity.client.strong.store');
        Route::post('/karate', [KarateClienteController::class, 'store'])->name('activity.client.karate.store');
        Route::post('/ingles', [InglesClienteController::class, 'store'])->name('activity.client.ingles.store');
        Route::post('/voleibol', [VoleibolClienteController::class, 'store'])->name('activity.client.voleibol.store');
        Route::post('/batting', [BattingClienteController::class, 'store'])->name('activity.client.batting.store');
        Route::post('/almaflamenca', [AlmaflamencaClienteController::class, 'store'])->name('activity.client.almaflamenca.store');
    });

    // === Actividades: registro de pagos (access-finanzas) ===
    Route::middleware('can:access-finanzas')->prefix('activity')->group(function () {
        Route::post('/natacion', [NatacionPagoController::class, 'store'])->name('activity.natacion.store');
        Route::post('/onbox', [OnboxPagoController::class, 'store'])->name('activity.onbox.store');
        Route::post('/lever', [LeverPagoController::class, 'store'])->name('activity.lever.store');
        Route::post('/pinpon', [PinponPagoController::class, 'store'])->name('activity.pinpon.store');
        Route::post('/basquet', [BasquetPagoController::class, 'store'])->name('activity.basquet.store');
        Route::post('/strong', [StrongPagoController::class, 'store'])->name('activity.strong.store');
        Route::post('/karate', [KaratePagoController::class, 'store'])->name('activity.karate.store');
        Route::post('/ingles', [InglesPagoController::class, 'store'])->name('activity.ingles.store');
        Route::post('/voleibol', [VoleibolPagoController::class, 'store'])->name('activity.voleibol.store');
        Route::post('/batting', [BattingPagoController::class, 'store'])->name('activity.batting.store');
        Route::post('/almaflamenca', [AlmaflamencoaPagoController::class, 'store'])->name('activity.almaflamenca.store');
    });
});
