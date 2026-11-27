<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\MockDataService;

class ReportController
{
    public function __construct()
    {
        Auth::requireAuth();
    }

    public function index(): void
    {
        $productModel  = new Product();
        $categoryModel = new Category();
        $saleModel     = new Sale();
        $movementModel = new StockMovement();

        $activeTab = $_GET['tab'] ?? 'stock';
        $allProducts = $productModel->getAll();
        $criticalStocks = $productModel->getCriticalStock();
        $sales = $saleModel->getAll(['limit' => 50]);
        $movements = $movementModel->getAll();
        $returns = MockDataService::all('returns');

        $totalInventoryValue = 0.0;
        foreach ($allProducts as $p) {
            $totalInventoryValue += (float)$p['current_stock'] * (float)$p['sale_price'];
        }

        view('report.index', [
            'pageTitle'           => 'Kırtasiye Raporları',
            'currentUser'         => Auth::user(),
            'activeTab'           => $activeTab,
            'products'            => $allProducts,
            'criticalStocks'      => $criticalStocks,
            'categories'          => $categoryModel->getAll(),
            'sales'               => $sales,
            'movements'           => $movements,
            'returns'             => $returns,
            'departments'         => [],
            'consumptionData'     => [],
            'totalInventoryValue' => $totalInventoryValue
        ]);
    }

    public function export(): void
    {
        $productModel = new Product();
        $products = $productModel->getAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="kirtasiye_stok_raporu_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($output, ['Kod', 'Barkod', 'Ürün Adı', 'Kategori', 'Marka', 'Mevcut Stok', 'Birim', 'Satış Fiyatı (TL)']);

        foreach ($products as $p) {
            fputcsv($output, [
                $p['code'],
                $p['barcode'],
                $p['name'],
                $p['category_name'],
                $p['brand_name'],
                $p['current_stock'],
                $p['unit'],
                $p['sale_price']
            ]);
        }

        fclose($output);
        exit;
    }

    public function pdf(): void
    {
        $productModel = new Product();
        $saleModel = new Sale();

        view('report.print', [
            'pageTitle'    => 'Kırtasiye Yazdırılabilir Rapor',
            'products'     => $productModel->getAll(),
            'sales'        => $saleModel->getAll(['limit' => 50]),
            'dailySummary' => $saleModel->getDailySummary()
        ]);
    }
}
