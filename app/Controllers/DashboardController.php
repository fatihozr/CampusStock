<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StockRequest;
use App\Models\Department;
use App\Config\Database;
use PDO;

class DashboardController
{
    /**
     * Show Dashboard page (Protected by Auth middleware)
     */
    public function index(): void
    {
        Auth::requireAuth();

        $currentUser     = Auth::user();
        $productModel    = new Product();
        $customerModel   = new Customer();
        $categoryModel   = new Category();
        $brandModel      = new Brand();
        $saleModel       = new Sale();
        $movementModel   = new StockMovement();
        $requestModel    = new StockRequest();
        $departmentModel = new Department();

        $allProducts        = $productModel->getAll();
        $totalStockCount    = count($allProducts);
        $criticalStocks     = $productModel->getCriticalStock(10);
        $criticalStockCount = count($criticalStocks);
        $customerCount      = count($customerModel->getAll());
        $dailySummary       = $saleModel->getDailySummary();

        // Stats array designed for view compatibility
        $stats = [
            'total_stock_items'  => $totalStockCount,
            'critical_stock'     => $criticalStockCount,
            'active_departments' => $customerCount, // Shown as Cariler/Müşteriler in dashboard
            'pending_requests'   => (int)($dailySummary['total_sales_count'] ?? 0) // Shown as Günlük Satışlar
        ];

        // Format recent movements for view
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

        $pendingRequests = $requestModel->getAll('pending');

        // Chart Data
        $chartTrendLabels = json_encode([]);
        $chartTrendData = json_encode([]);
        $chartDeptLabels = json_encode([]);
        $chartDeptData = json_encode([]);

        try {
            $db = Database::getConnection();
            if ($db) {
                // 1. Monthly movement trend
                $trendStmt = $db->query("
                    SELECT SUBSTR(created_at, 1, 7) as month, COUNT(*) as total_count 
                    FROM stock_movements 
                    GROUP BY SUBSTR(created_at, 1, 7)
                    ORDER BY month DESC 
                    LIMIT 6
                ");
                $trendData = array_reverse($trendStmt->fetchAll(PDO::FETCH_ASSOC));
                
                $tLabels = [];
                $tData = [];
                foreach ($trendData as $row) {
                    $tLabels[] = $row['month'];
                    $tData[] = (int)$row['total_count'];
                }
                if (empty($tLabels)) {
                    $tLabels = [date('Y-m')];
                    $tData = [$totalStockCount];
                }
                $chartTrendLabels = json_encode($tLabels);
                $chartTrendData = json_encode($tData);

                // 2. Categories product distribution
                $catStmt = $db->query("
                    SELECT c.name, COUNT(p.id) as total_qty 
                    FROM categories c 
                    LEFT JOIN products p ON p.category_id = c.id 
                    GROUP BY c.id, c.name
                    HAVING total_qty > 0
                    ORDER BY total_qty DESC 
                    LIMIT 5
                ");
                $topCats = $catStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $dLabels = [];
                $dData = [];
                foreach ($topCats as $row) {
                    $dLabels[] = $row['name'];
                    $dData[] = (int)$row['total_qty'];
                }
                $chartDeptLabels = json_encode($dLabels);
                $chartDeptData = json_encode($dData);
            }
        } catch (\Throwable $e) {
            error_log("Dashboard Chart Error: " . $e->getMessage());
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        view('dashboard.index', [
            'pageTitle'        => 'Gösterge Paneli | Kırtasiye Stok ve Satış Sistemi',
            'currentUser'      => $currentUser,
            'stats'            => $stats,
            'recentActivities' => $recentActivities,
            'pendingRequests'  => array_slice($pendingRequests, 0, 5),
            'criticalStocks'   => array_slice($criticalStocks, 0, 5),
            'chartTrendLabels' => $chartTrendLabels,
            'chartTrendData'   => $chartTrendData,
            'chartDeptLabels'  => $chartDeptLabels,
            'chartDeptData'    => $chartDeptData,
            'flashSuccess'     => $flashSuccess
        ]);
    }

    /**
     * API: Categorized Global Search across Products, Categories, and Users
     */
    public function globalSearch(): void
    {
        Auth::requireAuth();
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            echo json_encode(['success' => false, 'message' => 'En az 2 karakter giriniz.']);
            exit;
        }

        $qLower = mb_strtolower($query, 'UTF-8');

        $productModel  = new Product();
        $customerModel = new Customer();
        $userModel     = new \App\Models\User();

        $allProducts = $productModel->getAll();
        $matchedProducts = [];
        foreach ($allProducts as $st) {
            $nameStr = mb_strtolower($st['name'] ?? '', 'UTF-8');
            $codeStr = mb_strtolower($st['code'] ?? '', 'UTF-8');
            $barcodeStr = mb_strtolower($st['barcode'] ?? '', 'UTF-8');
            if (str_contains($nameStr, $qLower) || str_contains($codeStr, $qLower) || str_contains($barcodeStr, $qLower)) {
                $matchedProducts[] = [
                    'id'       => $st['id'],
                    'name'     => $st['name'],
                    'code'     => $st['code'] . ' (' . ($st['barcode'] ?? '') . ')',
                    'quantity' => $st['current_stock'],
                    'unit'     => $st['unit']
                ];
            }
        }

        $allCustomers = $customerModel->getAll();
        $matchedCustomers = [];
        foreach ($allCustomers as $c) {
            $nameStr = mb_strtolower($c['full_name'] ?? '', 'UTF-8');
            if (str_contains($nameStr, $qLower)) {
                $matchedCustomers[] = [
                    'id'         => $c['id'],
                    'full_name'  => $c['full_name'],
                    'department' => $c['type_label'] ?? 'Müşteri'
                ];
            }
        }

        $allUsers = $userModel->getAllUsers();
        $matchedUsers = [];
        foreach ($allUsers as $u) {
            $uName = mb_strtolower(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '') . ' ' . ($u['username'] ?? ''), 'UTF-8');
            if (str_contains($uName, $qLower)) {
                $matchedUsers[] = [
                    'id'        => $u['id'],
                    'full_name' => trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')),
                    'username'  => $u['username'],
                    'role_name' => $u['role_name'] ?? 'Kullanıcı'
                ];
            }
        }

        echo json_encode([
            'success'   => true,
            'products'  => array_slice($matchedProducts, 0, 5),
            'personnel' => array_slice($matchedCustomers, 0, 5),
            'users'     => array_slice($matchedUsers, 0, 3)
        ]);
        exit;
    }
}
