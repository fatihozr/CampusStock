<?php

namespace App\Controllers;

use App\Models\Sale;
use App\Services\PosService;
use App\Helpers\Auth;
use Exception;

class SaleController
{
    private Sale $saleModel;
    private PosService $posService;

    public function __construct()
    {
        Auth::requireAuth();
        $this->saleModel = new Sale();
        $this->posService = new PosService();
    }

    /**
     * Sales List & History
     */
    public function index(): void
    {
        $filters = [
            'status'         => $_GET['status'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'date_start'     => $_GET['date_start'] ?? null,
            'date_end'       => $_GET['date_end'] ?? null,
            'limit'          => 100
        ];

        $sales = $this->saleModel->getAll($filters);
        $dailySummary = $this->saleModel->getDailySummary();

        view('sales.index', [
            'pageTitle'    => 'Satış Geçmişi & Fişler',
            'sales'        => $sales,
            'dailySummary' => $dailySummary,
            'filters'      => $filters
        ]);
    }

    /**
     * Sale Receipt Detail
     */
    public function detail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $sale = $this->saleModel->find($id);

        if (!$sale) {
            redirect('/sales');
            return;
        }

        view('sales.detail', [
            'pageTitle' => 'Satış Detayı: ' . $sale['receipt_no'],
            'sale'      => $sale
        ]);
    }

    /**
     * Handle item return
     */
    public function handleReturn(): void
    {
        $saleId = (int)($_POST['sale_id'] ?? 0);
        $saleItemId = (int)($_POST['sale_item_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Müşteri talebi');
        $userId = Auth::user()['id'] ?? 1;

        try {
            $this->posService->processReturn($saleId, $saleItemId, $quantity, $reason, $userId);
            $_SESSION['flash_message'] = 'İade işlemi başarıyla gerçekleştirildi.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        redirect('/sales/detail?id=' . $saleId);
    }
}

