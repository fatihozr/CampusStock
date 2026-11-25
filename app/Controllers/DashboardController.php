<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\MockDataService;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAuth();

        $currentUser   = Auth::user();
        $productModel  = new Product();
        $customerModel = new Customer();
        $categoryModel = new Category();
        $saleModel     = new Sale();
        $movementModel = new StockMovement();

        $allProducts        = $productModel->getAll();
        $totalStockCount    = count($allProducts);
        $criticalStocks     = $productModel->getCriticalStock(10);
        $criticalStockCount = count($criticalStocks);
        $customerCount      = count($customerModel->getAll());
        $dailySummary       = $saleModel->getDailySummary();

        $universities = MockDataService::all('universities');
        $stores       = MockDataService::all('stores');
        $returns      = MockDataService::all('returns');
        $recentSales  = $saleModel->getAll(['limit' => 5]);

        $stats = [
            'total_stock_items'  => $totalStockCount,
            'critical_stock'     => $criticalStockCount,
            'active_departments' => $customerCount,
            'pending_requests'   => (int)($dailySummary['total_sales_count'] ?? 0),
            'daily_revenue'      => (float)($dailySummary['total_revenue'] ?? 0),
            'university_count'   => count($universities),
            'store_count'        => count($stores),
            'return_count'       => count($returns)
        ];

        $rawMovements = $movementModel->getRecent(6);
        $recentActivities = [];
        foreach ($rawMovements as $m) {
            $isIncome = in_array($m['movement_type'] ?? '', ['purchase', 'sale_return', 'in']);
            $recentActivities[] = [
                'type'            => $isIncome ? 'in' : 'out',
                'product_name'    => $m['product_name'] ?? 'Kırtasiye Ürünü',
                'department_name' => !empty($m['notes']) ? $m['notes'] : ($isIncome ? 'Depo Girişi' : 'POS Satışı'),
                'quantity'        => abs((float)($m['quantity'] ?? 0)),
                'created_at'      => $m['created_at'] ?? date('Y-m-d H:i:s')
            ];
        }

        // Category product distribution for pie chart
        $categories = $categoryModel->getAll();
        $dLabels = [];
        $dData = [];
        foreach ($categories as $cat) {
            if ((int)($cat['product_count'] ?? 0) > 0) {
                $dLabels[] = $cat['name'];
                $dData[]   = (int)$cat['product_count'];
            }
        }

        // Monthly trend mock chart
        $chartTrendLabels = json_encode([date('Y-m', strtotime('-2 months')), date('Y-m', strtotime('-1 month')), date('Y-m')]);
        $chartTrendData   = json_encode([14, 28, count($movementModel->getAll()) + 12]);
        $chartDeptLabels  = json_encode($dLabels);
        $chartDeptData    = json_encode($dData);

        view('dashboard.index', [
            'pageTitle'        => 'Kırtasiye Kontrol Paneli',
            'currentUser'      => $currentUser,
            'stats'            => $stats,
            'recentActivities' => $recentActivities,
            'recentSales'      => $recentSales,
            'criticalStocks'   => $criticalStocks,
            'stores'           => $stores,
            'pendingRequests'  => [],
            'chartTrendLabels' => $chartTrendLabels,
            'chartTrendData'   => $chartTrendData,
            'chartDeptLabels'  => $chartDeptLabels,
            'chartDeptData'    => $chartDeptData,
            'allStocks'        => $allProducts,
            'departments'      => []
        ]);
    }

    public function globalSearch(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json; charset=utf-8');

        $query = trim($_GET['q'] ?? '');
        if (mb_strlen($query) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }

        $productModel = new Product();
        $products = $productModel->getAll(['search' => $query, 'limit' => 8]);
        $results = [];
        foreach ($products as $p) {
            $results[] = [
                'type'     => 'product',
                'title'    => $p['name'],
                'subtitle' => "Barkod: {$p['barcode']} | Stok: {$p['current_stock']} {$p['unit']} | {$p['sale_price']} ₺",
                'url'      => base_url('products/detail?id=' . $p['id'])
            ];
        }

        echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
