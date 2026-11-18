<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Personnel;
use App\Models\Stock;

class DashboardController
{
    /**
     * Show Dashboard page (Protected by Auth middleware)
     */
    public function index(): void
    {
        Auth::requireAuth();

        $currentUser    = Auth::user();
        $stockModel     = new Stock();
        $personnelModel = new Personnel();

        $allStockItems = $stockModel->getAllItems();
        $totalStockCount = count($allStockItems);
        $criticalStockCount = $stockModel->getCriticalStockCount();
        $personnelCount = count($personnelModel->getAll());

        $stats = [
            'total_stock_items' => $totalStockCount,
            'critical_stock'    => $criticalStockCount,
            'active_departments'=> $personnelCount
        ];

        // Live recent activities feed from stock movements
        $movements = $stockModel->getMovements();
        $recentActivities = [];

        foreach (array_slice($movements, 0, 5) as $mov) {
            $recentActivities[] = [
                'id'          => $mov['document_no'],
                'item'        => $mov['item_name'],
                'category'    => $mov['category'],
                'action'      => $mov['movement_type'] === 'Giriş' ? 'Stok Girişi' : 'Stok Çıkışı',
                'person'      => $mov['target'],
                'date'        => $mov['date'],
                'badge_class' => $mov['movement_type'] === 'Giriş' ? 'bg-success' : 'bg-danger'
            ];
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        view('dashboard.index', [
            'pageTitle'        => 'Gösterge Paneli | Belediye Envanter Takip Sistemi',
            'currentUser'      => $currentUser,
            'stats'            => $stats,
            'recentActivities' => $recentActivities,
            'flashSuccess'     => $flashSuccess
        ]);
    }

    /**
     * API: Categorized Global Search across Stocks, Personnel, and Users
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

        $stockModel     = new Stock();
        $personnelModel = new Personnel();

        $allStocks = $stockModel->getAllItems();
        $matchedStocks = [];
        foreach ($allStocks as $st) {
            $nameStr = mb_strtolower($st['name'] ?? '', 'UTF-8');
            $codeStr = mb_strtolower($st['code'] ?? '', 'UTF-8');
            $rackStr = mb_strtolower($st['rack_location'] ?? '', 'UTF-8');
            if (str_contains($nameStr, $qLower) || str_contains($codeStr, $qLower) || str_contains($rackStr, $qLower)) {
                $matchedStocks[] = [
                    'id'            => $st['id'],
                    'name'          => $st['name'],
                    'code'          => $st['code'],
                    'quantity'      => $st['quantity'],
                    'unit'          => $st['unit'],
                    'rack_location' => $st['rack_location'] ?? 'C31'
                ];
            }
        }

        $allPersonnel = $personnelModel->getAll();
        $matchedPersonnel = [];
        foreach ($allPersonnel as $p) {
            $nameStr  = mb_strtolower($p['full_name'] ?? '', 'UTF-8');
            $sicilStr = mb_strtolower($p['sicil_no'] ?? '', 'UTF-8');
            $deptStr  = mb_strtolower($p['department'] ?? '', 'UTF-8');
            if (str_contains($nameStr, $qLower) || str_contains($sicilStr, $qLower) || str_contains($deptStr, $qLower)) {
                $matchedPersonnel[] = [
                    'id'         => $p['id'],
                    'full_name'  => $p['full_name'],
                    'sicil_no'   => $p['sicil_no'],
                    'department' => $p['department']
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
            'stocks'    => array_slice($matchedStocks, 0, 5),
            'personnel' => array_slice($matchedPersonnel, 0, 5),
            'users'     => array_slice($matchedUsers, 0, 3)
        ]);
        exit;
    }
}
