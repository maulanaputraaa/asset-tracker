<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CostEntryController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\BudgetController;

// Public routes (login/register)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes (butuh Sanctum token + tenant context)
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Users (FR-1.4: undang via email, aktif/nonaktif, assign role dan site)
    Route::apiResource('users', UserController::class);

    // Assets
    Route::apiResource('assets', AssetController::class);
    Route::post('assets/{asset}/qr-generate', [AssetController::class, 'generateQr']);
    
    // Transfers
    Route::apiResource('transfers', TransferController::class);
    Route::post('transfers/{transfer}/dispatch', [TransferController::class, 'dispatch']);
    Route::post('transfers/{transfer}/receive', [TransferController::class, 'receive']);
    
    // Work Orders
    Route::apiResource('work-orders', WorkOrderController::class);
    Route::post('work-orders/{workOrder}/start', [WorkOrderController::class, 'start']);
    Route::post('work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete']);
    
    // Stock Opname
    Route::apiResource('stock-opnames', StockOpnameController::class);
    Route::post('stock-opnames/{stockOpname}/scan', [StockOpnameController::class, 'scanAsset']);
    Route::post('stock-opnames/{stockOpname}/finalize', [StockOpnameController::class, 'finalize']);
    
    // Approvals
    Route::get('approvals/pending', [ApprovalController::class, 'pending']);
    Route::post('approvals/{approvalRequest}/decide', [ApprovalController::class, 'decide']);
    
    // Cost Tracking
    Route::apiResource('costs', CostEntryController::class);
    Route::get('assets/{asset}/tco', [CostEntryController::class, 'tco']);
    
    // Master Data
    Route::apiResource('categories', AssetCategoryController::class);
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('sites', SiteController::class);
    
    // Maintenance Schedule
    Route::apiResource('schedules', MaintenanceScheduleController::class);
    
    // Budgets
    Route::apiResource('budgets', BudgetController::class);
});