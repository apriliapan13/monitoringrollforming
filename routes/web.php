<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\DailyTargetController;
use App\Http\Controllers\ActualProductionController;
use App\Http\Controllers\CapacityController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('sales-orders', SalesOrderController::class);

    Route::get('/daily-targets', [DailyTargetController::class, 'index'])->name('daily-targets.index');
    Route::post('/daily-targets', [DailyTargetController::class, 'store'])->name('daily-targets.store');
    Route::delete('/daily-targets/{dailyTarget}', [DailyTargetController::class, 'destroy'])->name('daily-targets.destroy');

    Route::get('/actual-production', [ActualProductionController::class, 'index'])->name('actual-production.index');
    Route::post('/actual-production', [ActualProductionController::class, 'store'])->name('actual-production.store');
    Route::delete('/actual-production/{actualProduction}', [ActualProductionController::class, 'destroy'])->name('actual-production.destroy');

    Route::get('/capacity', [CapacityController::class, 'index'])->name('capacity.index');
    Route::get('/capacity/settings', [CapacityController::class, 'settings'])->name('capacity.settings');
    Route::post('/capacity/settings', [CapacityController::class, 'updateSettings'])->name('capacity.settings.update');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    Route::get('/export/pdf', [ExportController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});
