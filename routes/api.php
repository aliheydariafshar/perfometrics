<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentPerformanceController;
use App\Http\Controllers\Api\PerformanceCalculationController;
use App\Http\Controllers\Api\BatchStatusController;
use App\Http\Controllers\Api\EmployeePerformanceShowController;
use App\Http\Controllers\Api\EmployeePerformanceReportController;

Route::middleware('throttle:api-default')->group(function () {
    Route::get('/employees/{id}/performance', EmployeePerformanceShowController::class)
        ->name('employees.performance.show');
    Route::get('/departments/{id}/performance-summary', DepartmentPerformanceController::class)
        ->name('departments.performance.summary');
    Route::get('/performance/reports/{employee_id}', EmployeePerformanceReportController::class)
        ->name('employees.performance.report');
    Route::get('/performance/batches/{id}', BatchStatusController::class)
        ->name('performance.batches.show');
});

Route::post('/performance/calculate-all', PerformanceCalculationController::class)
    ->middleware('throttle:api-heavy')
    ->name('performance.calculate-all');
