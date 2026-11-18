<?php

namespace App\Controllers\Api;

use App\Models\Sale;
use App\Models\Product;
use App\Config\Database;
use PDO;
use Exception;

class ReportApiController extends BaseApiController
{
    private Sale $saleModel;
    private Product $productModel;
    private PDO $db;

    public function __construct()
    {
        $this->saleModel = new Sale();
        $this->productModel = new Product();
        $db = Database::getConnection();
        if (!$db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
        $this->db = $db;
    }

    /**
     * GET /api/v1/reports/daily-z-report
     * Returns daily sales, tax, gross profit, cash/card breakdown
     */
    public function dailyZReport(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $summary = $this->saleModel->getDailySummary($date);
        $summary['report_date'] = $date;

        $this->success($summary, 'Günlük Z Raporu getirildi');
    }

    /**
     * GET /api/v1/reports/top-selling
     */
    public function topSelling(): void
    {
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;

        $sql = "
            SELECT si.product_id, 
                   si.product_name, 
                   si.barcode,
                   SUM(si.quantity) AS total_sold_qty,
                   SUM(si.line_total) AS total_revenue
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            WHERE s.status != 'cancelled'
            GROUP BY si.product_id, si.product_name, si.barcode
            ORDER BY total_sold_qty DESC
            LIMIT {$limit}
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->success($rows, 'En çok satan ürünler listelendi');
    }

    /**
     * GET /api/v1/reports/critical-stock
     */
    public function criticalStock(): void
    {
        $critical = $this->productModel->getCriticalStock(100);
        $this->success($critical, 'Kritik stok seviyesindeki ürünler listelendi');
    }

    /**
     * GET /api/v1/reports/inventory-valuation
     */
    public function inventoryValuation(): void
    {
        $sql = "
            SELECT 
                COUNT(*) AS total_sku_count,
                COALESCE(SUM(current_stock), 0) AS total_physical_pieces,
                COALESCE(SUM(current_stock * purchase_price), 0) AS total_cost_value,
                COALESCE(SUM(current_stock * sale_price), 0) AS total_retail_value
            FROM products
            WHERE status = 'active'
        ";

        $stmt = $this->db->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $data['total_cost_value'] = (float)$data['total_cost_value'];
        $data['total_retail_value'] = (float)$data['total_retail_value'];
        $data['potential_profit'] = $data['total_retail_value'] - $data['total_cost_value'];

        $this->success($data, 'Stok envanter ve mağaza değerleme raporu');
    }
}
