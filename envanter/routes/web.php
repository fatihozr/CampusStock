<?php

use App\Config\Router;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\ImportController;
use App\Controllers\PersonnelController;
use App\Controllers\ReportController;
use App\Controllers\StockController;
use App\Controllers\TrashController;
use App\Controllers\UserController;

// Auth Routes
Router::get('/login', [AuthController::class, 'login']);
Router::post('/login', [AuthController::class, 'handleLogin']);
Router::get('/logout', [AuthController::class, 'logout']);

// Main Application Entry Point Route
Router::get('/', function () {
    if (\App\Helpers\Auth::check()) {
        redirect('/dashboard');
    } else {
        redirect('/login');
    }
});
Router::get('/dashboard', [DashboardController::class, 'index']);
Router::get('/api/global-search', [DashboardController::class, 'globalSearch']);

// Cleaning Supplies & Product Catalog Routes
Router::get('/stocks', [StockController::class, 'index']);
Router::get('/stocks/detail', [StockController::class, 'detail']);
Router::post('/stocks/store', [StockController::class, 'store']);
Router::post('/stocks/update', [StockController::class, 'update']);
Router::post('/stocks/stock-in', [StockController::class, 'stockIn']);
Router::post('/stocks/stock-out', [StockController::class, 'stockOut']);
Router::get('/stocks/movements', [StockController::class, 'movements']);
Router::post('/stocks/delete', [StockController::class, 'delete']);

// Category Management Routes
Router::get('/categories', [CategoryController::class, 'index']);
Router::post('/categories/store', [CategoryController::class, 'store']);

// AJAX Validation API Routes
Router::get('/api/check-product-code', [StockController::class, 'ajaxCheckCode']);
Router::get('/api/check-product-barcode', [StockController::class, 'ajaxCheckBarcode']);

// Personnel & Organization Module Routes
Router::get('/personnel', [PersonnelController::class, 'index']);
Router::get('/departments', [PersonnelController::class, 'departments']);
Router::post('/departments/store', [PersonnelController::class, 'storeDepartment']);
Router::post('/departments/update', [PersonnelController::class, 'updateDepartment']);
Router::post('/personnel/store', [PersonnelController::class, 'store']);
Router::post('/personnel/update', [PersonnelController::class, 'update']);
Router::post('/personnel/delete', [PersonnelController::class, 'delete']);
Router::get('/personnel/detail', [PersonnelController::class, 'detail']);

// User & Role Management Routes (RBAC)
Router::get('/users', [UserController::class, 'index']);
Router::post('/users/store', [UserController::class, 'storeUser']);
Router::post('/users/update', [UserController::class, 'updateUser']);
Router::post('/users/delete', [UserController::class, 'deleteUser']);
Router::post('/roles/permissions', [UserController::class, 'updateRolePermissions']);
Router::post('/settings/smtp/store', [UserController::class, 'storeSmtpSettings']);
Router::post('/settings/smtp/test', [UserController::class, 'testSmtpConnection']);

// Excel Import Module Routes
Router::get('/import', [ImportController::class, 'index']);
Router::post('/import/upload', [ImportController::class, 'upload']);
Router::post('/import/preview', [ImportController::class, 'preview']);
Router::post('/import/process', [ImportController::class, 'process']);
Router::get('/import/template', [ImportController::class, 'template']);

// Route Alias Safeguards
Router::get('/imports', function() { redirect('/import'); });
Router::get('/stock', function() { redirect('/stocks'); });

// Report & Export Routes (PDF, CSV, Excel, Email)
Router::get('/reports', [ReportController::class, 'index']);
Router::get('/reports/export', [ReportController::class, 'export']);
Router::get('/reports/pdf', [ReportController::class, 'pdf']);
Router::post('/reports/send-email', [ReportController::class, 'sendEmail']);

// Trash & Soft Delete Module Routes
Router::get('/trash', [TrashController::class, 'index']);
Router::post('/trash/restore', [TrashController::class, 'restore']);
Router::post('/trash/purge', [TrashController::class, 'purge']);
