<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\PartnerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {return $request->user();})->middleware('auth:sanctum');


// --- Public route ---
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');


Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    // Partners
    Route::apiResource('/partners', PartnerController::class);
    // Family Partners
    Route::apiResource('/family', FamilyController::class);
    // Manager
    Route::apiResource('/manager', ManagerController::class);
});


