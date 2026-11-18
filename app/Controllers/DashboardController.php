<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Product;
use App\Models\Department;
use App\Models\StockMovement;
use App\Models\StockRequest;

class DashboardController
{
    /**
     * Show Dashboard page (Protected by Auth middleware)
     */
    public function index(): void
    {
        Auth::requireAuth();

        $currentUser    = Auth::user();
        $productModel   = new Product();
        $departmentModel= new Department();
        $movementModel  = new StockMovement();
        $requestModel   = new StockRequest();

        $allProducts = $productModel->getAll();
        $totalStockCount = count($allProducts);
        $criticalStocks = $productModel->getCriticalStock();
        $criticalStockCount = count($criticalStocks);
        $departmentCount = count($departmentModel->getAll());

        $stats = [
            'total_stock_items' => $totalStockCount,
            'critical_stock'    => $criticalStockCount,
            'active_departments'=> $departmentCount,
            'pending_requests'  => count($requestModel->getAll('pending'))
        ];

        // Live recent activities feed from stock movements
        $recentActivities = $movementModel->getRecent(5);
        $pendingRequests = $requestModel->getAll('pending');

        // Fetch Data for Charts (wrapped in try-catch to prevent dashboard crash)
        $chartTrendLabels = json_encode([]);
        $chartTrendData = json_encode([]);
        $chartDeptLabels = json_encode([]);
        $chartDeptData = json_encode([]);

        try {
            $db = \App\Config\Database::getConnection();
            if ($db) {
                // 1. Monthly Consumption Trend (last 6 months)
                $trendStmt = $db->query("
                    SELECT SUBSTR(created_at, 1, 7) as month, SUM(quantity) as total_qty 
                    FROM stock_movements 
                    WHERE type = 'out' 
                    GROUP BY SUBSTR(created_at, 1, 7)
                    ORDER BY month DESC 
                    LIMIT 6
                ");
                $trendData = array_reverse($trendStmt->fetchAll(\PDO::FETCH_ASSOC));
                
                $tLabels = [];
                $tData = [];
                foreach($trendData as $row) {
                    $tLabels[] = $row['month'];
                    $tData[] = (int)$row['total_qty'];
                }
                $chartTrendLabels = json_encode($tLabels);
                $chartTrendData = json_encode($tData);

                // 2. Top Consuming Departments
                $deptStmt = $db->query("
                    SELECT d.name, SUM(sm.quantity) as total_qty 
                    FROM stock_movements sm 
                    JOIN departments d ON sm.department_id = d.id 
                    WHERE sm.type = 'out' 
                    GROUP BY d.id, d.name
                    ORDER BY total_qty DESC 
                    LIMIT 5
                ");
                $topDepts = $deptStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $dLabels = [];
                $dData = [];
                foreach($topDepts as $row) {
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
            'pageTitle'        => 'Gösterge Paneli | Stationery Flow E-Ticaret ve Depo',
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
     * API: Categorized Global Search across Products, Departments, and Users
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

        $productModel     = new Product();
        $departmentModel = new Department();

        $allProducts = $productModel->getAll();
        $matchedProducts = [];
        foreach ($allProducts as $st) {
            $nameStr = mb_strtolower($st['name'] ?? '', 'UTF-8');
            $codeStr = mb_strtolower($st['code'] ?? '', 'UTF-8');
            if (str_contains($nameStr, $qLower) || str_contains($codeStr, $qLower)) {
                $matchedProducts[] = [
                    'id'            => $st['id'],
                    'name'          => $st['name'],
                    'code'          => $st['code'],
                    'quantity'      => $st['current_stock'],
                    'unit'          => $st['unit']
                ];
            }
        }

        $allDepartments = $departmentModel->getAll();
        $matchedDepartments = [];
        foreach ($allDepartments as $d) {
            $nameStr  = mb_strtolower($d['name'] ?? '', 'UTF-8');
            if (str_contains($nameStr, $qLower)) {
                $matchedDepartments[] = [
                    'id'         => $d['id'],
                    'full_name'  => $d['name'],
                    'department' => $d['name']
                ];
            }
        }

        $userModel = new \App\Models\User();
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
            'personnel' => array_slice($matchedDepartments, 0, 5),
            'users'     => array_slice($matchedUsers, 0, 3)
        ]);
        exit;
    }
}

