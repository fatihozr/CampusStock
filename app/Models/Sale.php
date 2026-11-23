<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Sale
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
    }

    /**
     * Get all sales with optional filters
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT s.*, 
                   c.first_name AS customer_first_name, 
                   c.last_name AS customer_last_name, 
                   c.company_name AS customer_company,
                   c.customer_type,
                   u.username AS cashier_username,
                   u.first_name AS cashier_first_name,
                   u.last_name AS cashier_last_name,
                   (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $sql .= " AND s.payment_method = :payment_method";
            $params['payment_method'] = $filters['payment_method'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND s.customer_id = :customer_id";
            $params['customer_id'] = (int)$filters['customer_id'];
        }

        if (!empty($filters['date_start'])) {
            $sql .= " AND s.created_at >= :date_start";
            $params['date_start'] = $filters['date_start'] . ' 00:00:00';
        }

        if (!empty($filters['date_end'])) {
            $sql .= " AND s.created_at <= :date_end";
            $params['date_end'] = $filters['date_end'] . ' 23:59:59';
        }

        $sql .= " ORDER BY s.id DESC";

        if (!empty($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = (int)($filters['offset'] ?? 0);
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'formatSale'], $rows);
    }

    public function formatSale(array $s): array
    {
        $s['customer_display'] = !empty($s['customer_company'])
            ? $s['customer_company']
            : (!empty($s['customer_first_name']) ? trim($s['customer_first_name'] . ' ' . ($s['customer_last_name'] ?? '')) : 'Perakende Müşteri');

        $s['cashier_display'] = !empty($s['cashier_first_name']) 
            ? trim($s['cashier_first_name'] . ' ' . ($s['cashier_last_name'] ?? '')) 
            : ($s['cashier_username'] ?? 'Kasiyer');

        $s['payment_label'] = match($s['payment_method']) {
            'cash'          => 'Nakit',
            'credit_card'   => 'Kredi Kartı',
            'bank_transfer' => 'Havale / EFT',
            'open_account'  => 'Veresiye / Açık Hesap',
            'split'         => 'Parçalı Ödeme',
            default         => $s['payment_method']
        };

        $s['status_label'] = match($s['status']) {
            'completed'          => 'Tamamlandı',
            'refunded'           => 'İade Edildi',
            'partially_refunded' => 'Kısmi İade',
            'cancelled'          => 'İptal',
            default              => $s['status']
        };

        $s['status_badge'] = match($s['status']) {
            'completed'          => 'bg-success text-white',
            'refunded'           => 'bg-danger text-white',
            'partially_refunded' => 'bg-warning text-dark',
            'cancelled'          => 'bg-secondary text-white',
            default              => 'bg-light text-dark'
        };

        $s['subtotal'] = (float)$s['subtotal'];
        $s['tax_total'] = (float)$s['tax_total'];
        $s['discount_total'] = (float)$s['discount_total'];
        $s['grand_total'] = (float)$s['grand_total'];
        $s['paid_amount'] = (float)$s['paid_amount'];
        $s['change_amount'] = (float)$s['change_amount'];

        return $s;
    }

    /**
     * Find sale by ID with its items
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, 
                   c.first_name AS customer_first_name, 
                   c.last_name AS customer_last_name, 
                   c.company_name AS customer_company,
                   c.phone AS customer_phone,
                   c.email AS customer_email,
                   c.customer_type,
                   u.username AS cashier_username,
                   u.first_name AS cashier_first_name,
                   u.last_name AS cashier_last_name
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $sale = $this->formatSale($row);

        // Fetch sale items
        $itemStmt = $this->db->prepare("
            SELECT si.*, p.code AS product_code, p.image_url
            FROM sale_items si
            LEFT JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = :sale_id
            ORDER BY si.id ASC
        ");
        $itemStmt->execute(['sale_id' => $id]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $sale['items'] = array_map(function($i) {
            $i['quantity'] = (float)$i['quantity'];
            $i['unit_price'] = (float)$i['unit_price'];
            $i['cost_price'] = (float)$i['cost_price'];
            $i['tax_rate'] = (float)$i['tax_rate'];
            $i['tax_amount'] = (float)$i['tax_amount'];
            $i['discount_amount'] = (float)$i['discount_amount'];
            $i['line_total'] = (float)$i['line_total'];
            $i['returned_quantity'] = (float)$i['returned_quantity'];
            return $i;
        }, $items);

        return $sale;
    }

    /**
     * Find sale by Receipt Number
     */
    public function findByReceiptNo(string $receiptNo): ?array
    {
        $stmt = $this->db->prepare("SELECT id FROM sales WHERE receipt_no = :receipt_no LIMIT 1");
        $stmt->execute(['receipt_no' => trim($receiptNo)]);
        $id = $stmt->fetchColumn();

        return $id ? $this->find((int)$id) : null;
    }

    /**
     * Generate unique receipt number (e.g. KRT-20261118-0003)
     */
    public function generateReceiptNo(): string
    {
        $prefix = 'KRT-' . date('Ymd') . '-';
        $stmt = $this->db->prepare("SELECT receipt_no FROM sales WHERE receipt_no LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastNo = $stmt->fetchColumn();

        if ($lastNo) {
            $lastSeq = (int)substr($lastNo, strlen($prefix));
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Get Daily Sales Summary / Z-Report Data
     */
    public function getDailySummary(?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS total_sales_count,
                COALESCE(SUM(grand_total), 0) AS total_revenue,
                COALESCE(SUM(tax_total), 0) AS total_tax,
                COALESCE(SUM(discount_total), 0) AS total_discounts,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN grand_total ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'credit_card' THEN grand_total ELSE 0 END), 0) AS card_total,
                COALESCE(SUM(CASE WHEN payment_method = 'open_account' THEN grand_total ELSE 0 END), 0) AS open_account_total,
                COALESCE(SUM(CASE WHEN payment_method = 'bank_transfer' THEN grand_total ELSE 0 END), 0) AS transfer_total
            FROM sales
            WHERE created_at BETWEEN :start AND :end AND status != 'cancelled'
        ");
        $stmt->execute(['start' => $start, 'end' => $end]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate Cost & Profit
        $costStmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(si.quantity * si.cost_price), 0) AS total_cost
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            WHERE s.created_at BETWEEN :start AND :end AND s.status != 'cancelled'
        ");
        $costStmt->execute(['start' => $start, 'end' => $end]);
        $totalCost = (float)$costStmt->fetchColumn();

        $stats['total_revenue'] = (float)$stats['total_revenue'];
        $stats['total_cost'] = $totalCost;
        $stats['gross_profit'] = $stats['total_revenue'] - $totalCost;
        $stats['profit_margin'] = $stats['total_revenue'] > 0 
            ? round(($stats['gross_profit'] / $stats['total_revenue']) * 100, 2) 
            : 0.0;

        return $stats;
    }
}

