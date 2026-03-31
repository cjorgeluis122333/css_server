<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HallControlController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\HistoryPayController;
use App\Http\Controllers\ManagerBoardsController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\RegisteredGuestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- Public route ---
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    // Partners
    Route::apiResource('/partners', PartnerController::class);
    Route::get('/partners-access', [PartnerController::class, 'access_controller'])->name('partners.access');
    // Family Partners
    Route::apiResource('/family', FamilyController::class);
    // Manager
    Route::apiResource('/manager', ManagerController::class);
    // Manager Boards
    Route::apiResource('/board', ManagerBoardsController::class);
    // History
    Route::apiResource('/history', HistoryPayController::class);
    // Hall
    Route::apiResource('/halls-pay', HallController::class);
    Route::apiResource('/halls-control', HallControlController::class);
    // Fee
    Route::get('/fee/showByMonth/{mes?}', [FeeController::class, 'showByMonth'])->name('showByMonth');
    Route::apiResource('fee', FeeController::class);
    // Debt
    Route::get('/partners/debs/titular-summary', [PartnerController::class, 'titularDebtSummary'])->name('partners.titularDebtSummary');
    Route::get('/partners/debs/{id}', [PartnerController::class, 'showDebts'])->name('showDebts');
    Route::get('/partners/debs/advance/{id}', [PartnerController::class, 'getAdvanceQuotes'])->name('partners.advanceQuotes');
    // Guest
    Route::get('/guest/{acc}', [GuestController::class, 'index'])->name('guest.index');
    Route::get('/guest-current/{acc}', [GuestController::class, 'currentMonth'])->name('guest.current-month');
    Route::apiResource('/guest', GuestController::class);
    // Register Guests
    Route::apiResource('/register-guest', RegisteredGuestController::class);

});
