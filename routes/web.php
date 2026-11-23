<?php

use App\Config\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PosController;
use App\Controllers\SaleController;
use App\Controllers\StockController;
use App\Controllers\CategoryController;
use App\Controllers\BrandController;
use App\Controllers\CustomerController;
use App\Controllers\ReportController;
use App\Controllers\ImportController;
use App\Controllers\UserController;

// API Controllers
use App\Controllers\Api\ProductApiController;
use App\Controllers\Api\CategoryApiController;
use App\Controllers\Api\BrandApiController;
use App\Controllers\Api\PosApiController;
use App\Controllers\Api\CustomerApiController;
use App\Controllers\Api\ReportApiController;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\UniversityApiController;
use App\Controllers\Api\StoreApiController;
use App\Controllers\Api\OrderApiController;
use App\Controllers\Api\PrintJobApiController;

// ==========================================================
// 1. KULLANICI KİMLİK DOĞRULAMA (AUTH) ROTALARI
// ==========================================================
Router::get('/login', [AuthController::class, 'login']);
Router::post('/login', [AuthController::class, 'handleLogin']);
Router::get('/logout', [AuthController::class, 'logout']);

Router::get('/', function () {
    if (\App\Helpers\Auth::check()) {
        redirect('/dashboard');
    } else {
        redirect('/login');
    }
});

// ==========================================================
// 2. YÖNETİM PANELİ VE RAPORLAMA ROTALARI
// ==========================================================
Router::get('/dashboard', [DashboardController::class, 'index']);
Router::get('/api/global-search', [DashboardController::class, 'globalSearch']);

// ==========================================================
// 3. KIRTASİYE HIZLI SATIŞ (POS) & SATIŞ GEÇMİŞİ ROTALARI
// ==========================================================
Router::get('/pos', [PosController::class, 'index']);
Router::get('/sales', [SaleController::class, 'index']);
Router::get('/sales/detail', [SaleController::class, 'detail']);
Router::post('/sales/return', [SaleController::class, 'handleReturn']);

// ==========================================================
// 4. KIRTASİYE ÜRÜN VE STOK YÖNETİMİ ROTALARI
// ==========================================================
Router::get('/products', [StockController::class, 'index']);
Router::get('/products/create', [StockController::class, 'create']);
Router::post('/products/store', [StockController::class, 'store']);
Router::get('/products/detail', [StockController::class, 'detail']);
Router::post('/products/update', [StockController::class, 'update']);
Router::post('/products/delete', [StockController::class, 'delete']);
Router::post('/products/stock-in', [StockController::class, 'stockIn']);
Router::post('/products/stock-out', [StockController::class, 'stockOut']);
Router::get('/products/movements', [StockController::class, 'movements']);

// ==========================================================
// 5. KATEGORİ, MARKA VE MÜŞTERİ (CARİ) ROTALARI
// ==========================================================
Router::get('/categories', [CategoryController::class, 'index']);
Router::post('/categories/store', [CategoryController::class, 'store']);

Router::get('/brands', [BrandController::class, 'index']);
Router::post('/brands/store', [BrandController::class, 'store']);

Router::get('/customers', [CustomerController::class, 'index']);
Router::post('/customers/store', [CustomerController::class, 'store']);
Router::post('/customers/payment', [CustomerController::class, 'handlePayment']);

// Raporlar & Dışa Aktarım
Router::get('/reports', [ReportController::class, 'index']);
Router::get('/reports/export', [ReportController::class, 'export']);
Router::get('/reports/pdf', [ReportController::class, 'pdf']);

// Excel İçe Aktarım
Router::get('/import', [ImportController::class, 'index']);
Router::post('/import/upload', [ImportController::class, 'upload']);
Router::post('/import/preview', [ImportController::class, 'preview']);
Router::post('/import/process', [ImportController::class, 'process']);
Router::get('/import/template', [ImportController::class, 'template']);

// Kullanıcılar
Router::get('/users', [UserController::class, 'index']);
Router::post('/users/store', [UserController::class, 'storeUser']);
Router::post('/users/update', [UserController::class, 'updateUser']);
Router::post('/users/delete', [UserController::class, 'deleteUser']);

// ==========================================================
// 6. FRONTEND İÇİN RESTFUL JSON API (V1) ROTALARI
// ==========================================================

// Kimlik Doğrulama & Kullanıcı API (Auth)
Router::post('/api/v1/auth/login', [AuthApiController::class, 'login']);
Router::post('/api/v1/auth/register', [AuthApiController::class, 'register']);
Router::get('/api/v1/auth/me', [AuthApiController::class, 'me']);
Router::post('/api/v1/auth/logout', [AuthApiController::class, 'logout']);

// Üniversiteler & Kampüsler API
Router::get('/api/v1/universities', [UniversityApiController::class, 'index']);
Router::post('/api/v1/universities', [UniversityApiController::class, 'store']);
Router::get('/api/v1/universities/{id}', [UniversityApiController::class, 'show']);
Router::get('/api/v1/universities/{id}/stores', [UniversityApiController::class, 'stores']);

// Kampüs Mağazaları API
Router::get('/api/v1/stores', [StoreApiController::class, 'index']);
Router::get('/api/v1/stores/{id}', [StoreApiController::class, 'show']);
Router::get('/api/v1/stores/{id}/products', [StoreApiController::class, 'products']);

// Öğrenci Gel-Al Siparişleri API
Router::get('/api/v1/orders', [OrderApiController::class, 'index']);
Router::post('/api/v1/orders', [OrderApiController::class, 'store']);
Router::post('/api/v1/orders/verify-pickup', [OrderApiController::class, 'verifyPickup']);
Router::get('/api/v1/orders/{id}', [OrderApiController::class, 'show']);
Router::put('/api/v1/orders/{id}/status', [OrderApiController::class, 'updateStatus']);
Router::post('/api/v1/orders/{id}/status', [OrderApiController::class, 'updateStatus']);

// Fotokopi & Belge Baskı Hizmeti API
Router::get('/api/v1/print-jobs/pricing', [PrintJobApiController::class, 'pricing']);
Router::post('/api/v1/print-jobs/calculate', [PrintJobApiController::class, 'calculate']);
Router::get('/api/v1/print-jobs', [PrintJobApiController::class, 'index']);
Router::post('/api/v1/print-jobs', [PrintJobApiController::class, 'store']);
Router::get('/api/v1/print-jobs/{id}', [PrintJobApiController::class, 'show']);
Router::put('/api/v1/print-jobs/{id}/status', [PrintJobApiController::class, 'updateStatus']);
Router::post('/api/v1/print-jobs/{id}/status', [PrintJobApiController::class, 'updateStatus']);

// Ürün API
Router::get('/api/v1/products', [ProductApiController::class, 'index']);
Router::post('/api/v1/products', [ProductApiController::class, 'store']);
Router::get('/api/v1/products/barcode/{barcode}', [ProductApiController::class, 'barcode']);
Router::get('/api/v1/products/{id}', [ProductApiController::class, 'show']);
Router::put('/api/v1/products/{id}', [ProductApiController::class, 'update']);
Router::post('/api/v1/products/update', [ProductApiController::class, 'update']);
Router::delete('/api/v1/products/{id}', [ProductApiController::class, 'destroy']);
Router::post('/api/v1/products/adjust-stock', [ProductApiController::class, 'adjustStock']);

// Kategori & Marka API
Router::get('/api/v1/categories', [CategoryApiController::class, 'index']);
Router::post('/api/v1/categories', [CategoryApiController::class, 'store']);
Router::get('/api/v1/brands', [BrandApiController::class, 'index']);
Router::post('/api/v1/brands', [BrandApiController::class, 'store']);

// POS, Sepet & Satış API
Router::post('/api/v1/pos/calculate', [PosApiController::class, 'calculate']);
Router::post('/api/v1/pos/checkout', [PosApiController::class, 'checkout']);
Router::get('/api/v1/pos/sales', [PosApiController::class, 'sales']);
Router::get('/api/v1/pos/sales/{id}', [PosApiController::class, 'saleDetail']);
Router::post('/api/v1/pos/sales/{id}/return', [PosApiController::class, 'returnItem']);

// Müşteri / Cari API
Router::get('/api/v1/customers', [CustomerApiController::class, 'index']);
Router::post('/api/v1/customers', [CustomerApiController::class, 'store']);
Router::get('/api/v1/customers/{id}', [CustomerApiController::class, 'show']);
Router::post('/api/v1/customers/{id}/payment', [CustomerApiController::class, 'payment']);

// Rapor API
Router::get('/api/v1/reports/daily-z-report', [ReportApiController::class, 'dailyZReport']);
Router::get('/api/v1/reports/top-selling', [ReportApiController::class, 'topSelling']);
Router::get('/api/v1/reports/critical-stock', [ReportApiController::class, 'criticalStock']);
Router::get('/api/v1/reports/inventory-valuation', [ReportApiController::class, 'inventoryValuation']);

// Kısayol Yönlendirmeleri
Router::get('/stock', function() { redirect('/products'); });
Router::get('/stocks', function() { redirect('/products'); });
Router::get('/kurulum', function() {
    require_once dirname(__DIR__) . '/veritabani_ve_kurulum/kirtasiye_kurulum.php';
});
