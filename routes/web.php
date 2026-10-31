<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ComicController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CashierDashboardController;
use App\Http\Controllers\PosController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect(auth()->user()->role === 'admin' ? route('admin.dashboard') : route('kasir.dashboard'));
});
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('comics', ComicController::class);
    Route::resource('categories', CategoryController::class)->except(['create','show','edit']);
    Route::resource('users', UserController::class)->except(['create','show','edit']);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
});

Route::middleware(['auth','role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [CashierDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::get('/products/search', [PosController::class, 'search'])->name('products.search');
    Route::get('/held', [PosController::class, 'held'])->name('held');
    Route::delete('/held/{heldTransaction}', [PosController::class, 'destroyHeld'])->name('held.destroy');
    Route::post('/pos/hold', [PosController::class, 'hold'])->name('hold');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('checkout');
    Route::get('/history', [PosController::class, 'history'])->name('history');
    Route::get('/receipt/{sale}', [PosController::class, 'receipt'])->name('receipt');
    Route::get('/receipt/{sale}/download', [PosController::class, 'downloadReceipt'])->name('receipt.download');
    Route::post('/receipt/{sale}/print', \App\Http\Controllers\ReceiptPrintController::class)->name('receipt.print');
    Route::get('/printers', \App\Http\Controllers\PrinterListController::class)->name('printers');
});
