<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LoanRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/equipments');

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');

Route::resource('equipments', equipmentsController::class)->only(['index', 'show']);
Route::resource('kategori', KategoriController::class)->only(['index', 'show']);

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::resource('equipments', equipmentsController::class)->except(['index', 'show']);
    Route::resource('kategori', KategoriController::class)->except(['index', 'show']);

    Route::resource('loan_request', LoanRequestController::class)
        ->only(['index', 'create', 'store']);
    Route::post('/loan_request/{loan_request}/pengembalian', [Loan_requestController::class, 'returnEquipments'])
        ->name('loan_request.return');
});
