<?php

namespace App\Controllers\Api;

use App\Services\PosService;
use App\Models\Sale;
use Exception;

class PosApiController extends BaseApiController
{
    private PosService $posService;
    private Sale $saleModel;

    public function __construct()
    {
        $this->posService = new PosService();
        $this->saleModel = new Sale();
    }

    /**
     * POST /api/v1/pos/calculate
     * Realtime cart calculation: receives items array, customer_id, discount_rate
     * Returns computed items, tax breakdown, subtotal, discount, grand total
     */
    public function calculate(): void
    {
        $body = $this->getJsonBody();
        $items = $body['items'] ?? [];
        $customerId = !empty($body['customer_id']) ? (int)$body['customer_id'] : null;
        $discountRate = (float)($body['discount_rate'] ?? 0.0);

        try {
            $calculation = $this->posService->calculateCart($items, $customerId, $discountRate);
            $this->success($calculation, 'Sepet başarıyla hesaplandı');
        } catch (Exception $e) {
            $this->error('Sepet hesaplama hatası: ' . $e->getMessage(), 400);
        }
    }

    /**
     * POST /api/v1/pos/checkout
     * Finalizes the sale, deducts stock, produces receipt
     */
    public function checkout(): void
    {
        $body = $this->getJsonBody();
        $items = $body['items'] ?? [];

        if (empty($items)) {
            $this->error('Sepet boş olamaz', 422);
        }

        // Active cashier ID or default to 1
        $userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 1;

        try {
            $result = $this->posService->checkout($body, $userId);
            $this->success($result, 'Satış başarıyla tamamlandı ve fiş oluşturuldu', 201);
        } catch (Exception $e) {
            $this->error('Satış tamamlanamadı: ' . $e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/pos/sales
     * Sales history list with filters
     */
    public function sales(): void
    {
        $filters = [
            'status'         => $_GET['status'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'customer_id'    => !empty($_GET['customer_id']) ? (int)$_GET['customer_id'] : null,
            'date_start'     => $_GET['date_start'] ?? null,
            'date_end'       => $_GET['date_end'] ?? null,
            'limit'          => !empty($_GET['limit']) ? (int)$_GET['limit'] : 50,
            'offset'         => !empty($_GET['offset']) ? (int)$_GET['offset'] : 0
        ];

        $sales = $this->saleModel->getAll($filters);
        $this->success($sales, 'Satışlar listelendi');
    }

    /**
     * GET /api/v1/pos/sales/{id}
     * Full sale receipt details
     */
    public function saleDetail(?string $id = null): void
    {
        $saleId = $this->getParamInt($id, 'id');
        if (!$saleId) {
            $this->error('Geçersiz satış ID', 400);
        }

        $sale = $this->saleModel->find($saleId);
        if (!$sale) {
            $this->error('Satış fişi bulunamadı', 404);
        }

        $this->success($sale, 'Satış detayları getirildi');
    }

    /**
     * POST /api/v1/pos/sales/{id}/return
     * Process return of an item
     */
    public function returnItem(?string $id = null): void
    {
        $saleId = $this->getParamInt($id, 'id');
        $body = $this->getJsonBody();

        $saleItemId = (int)($body['sale_item_id'] ?? 0);
        $quantity = (float)($body['quantity'] ?? 0);
        $reason = trim($body['reason'] ?? 'Müşteri talebi / İade');
        $userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 1;

        if (!$saleId || !$saleItemId || $quantity <= 0) {
            $this->error('Satış ID, Satış Kalem ID ve geçerli miktar zorunludur', 422);
        }

        try {
            $result = $this->posService->processReturn($saleId, $saleItemId, $quantity, $reason, $userId);
            $this->success($result, 'İade işlemi başarıyla tamamlandı ve stok güncellendi');
        } catch (Exception $e) {
            $this->error('İade işlemi yapılamadı: ' . $e->getMessage(), 400);
        }
    }
}

