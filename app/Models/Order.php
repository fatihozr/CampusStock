<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Throwable;
use Exception;

class Order
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        if (!$this->db) return;

        try {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS orders (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        order_no TEXT UNIQUE NOT NULL,
                        student_id INTEGER NOT NULL,
                        store_id INTEGER NOT NULL DEFAULT 1,
                        total_amount REAL NOT NULL DEFAULT 0.0,
                        discount_amount REAL NOT NULL DEFAULT 0.0,
                        final_amount REAL NOT NULL DEFAULT 0.0,
                        payment_status TEXT DEFAULT 'pending',
                        payment_method TEXT DEFAULT 'cash_pickup',
                        order_status TEXT DEFAULT 'received',
                        pickup_code TEXT NOT NULL,
                        notes TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                    CREATE TABLE IF NOT EXISTS order_items (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        order_id INTEGER NOT NULL,
                        product_id INTEGER NOT NULL,
                        product_name TEXT NOT NULL,
                        quantity REAL NOT NULL,
                        unit_price REAL NOT NULL,
                        line_total REAL NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        order_no VARCHAR(60) UNIQUE NOT NULL,
                        student_id INT NOT NULL,
                        store_id INT NOT NULL DEFAULT 1,
                        total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                        discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                        final_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                        payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
                        payment_method ENUM('online_card', 'cash_pickup', 'card_pickup') DEFAULT 'cash_pickup',
                        order_status ENUM('received', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'received',
                        pickup_code VARCHAR(10) NOT NULL,
                        notes TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_order_student (student_id),
                        INDEX idx_order_store (store_id),
                        INDEX idx_order_pickup (pickup_code)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE IF NOT EXISTS order_items (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        order_id INT NOT NULL,
                        product_id INT NOT NULL,
                        product_name VARCHAR(255) NOT NULL,
                        quantity DECIMAL(10,2) NOT NULL,
                        unit_price DECIMAL(10,2) NOT NULL,
                        line_total DECIMAL(10,2) NOT NULL,
                        INDEX idx_ord_item_ord (order_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }
        } catch (Throwable) {}
    }

    /**
     * Create atomic Order with Items
     */
    public function createOrder(array $orderData, array $items): array
    {
        $this->db->beginTransaction();
        try {
            $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $pickupCode = 'CS-' . rand(1000, 9999);

            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    order_no, student_id, store_id, total_amount, discount_amount,
                    final_amount, payment_status, payment_method, order_status, pickup_code, notes
                ) VALUES (
                    :order_no, :student_id, :store_id, :total_amount, :discount_amount,
                    :final_amount, :payment_status, :payment_method, :order_status, :pickup_code, :notes
                )
            ");

            $stmt->execute([
                'order_no'        => $orderNo,
                'student_id'      => (int)$orderData['student_id'],
                'store_id'        => (int)($orderData['store_id'] ?? 1),
                'total_amount'    => (float)($orderData['total_amount'] ?? 0.0),
                'discount_amount' => (float)($orderData['discount_amount'] ?? 0.0),
                'final_amount'    => (float)($orderData['final_amount'] ?? 0.0),
                'payment_status'  => $orderData['payment_status'] ?? 'pending',
                'payment_method'  => $orderData['payment_method'] ?? 'cash_pickup',
                'order_status'    => 'received',
                'pickup_code'     => $pickupCode,
                'notes'           => $orderData['notes'] ?? null
            ]);

            $orderId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total)
                VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :line_total)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id'     => $orderId,
                    'product_id'   => (int)$item['product_id'],
                    'product_name' => $item['product_name'] ?? 'Ürün',
                    'quantity'     => (float)$item['quantity'],
                    'unit_price'   => (float)$item['unit_price'],
                    'line_total'   => (float)$item['line_total']
                ]);
            }

            $this->db->commit();

            return $this->findById($orderId);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw new Exception("Sipariş oluşturulamadı: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, 
                       u.username as student_username, u.first_name as student_first_name, u.last_name as student_last_name, u.email as student_email, u.phone as student_phone,
                       s.name as store_name
                FROM orders o
                LEFT JOIN users u ON o.student_id = u.id
                LEFT JOIN stores s ON o.store_id = s.id
                WHERE o.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                $order['items'] = $this->getOrderItems($id);
                return $order;
            }
        } catch (Throwable) {}

        return null;
    }

    public function findByOrderNo(string $orderNo): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM orders WHERE order_no = :order_no LIMIT 1");
            $stmt->execute(['order_no' => trim($orderNo)]);
            $id = $stmt->fetchColumn();
            if ($id) {
                return $this->findById((int)$id);
            }
        } catch (Throwable) {}

        return null;
    }

    public function findByPickupCode(string $code): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM orders WHERE pickup_code = :code ORDER BY id DESC LIMIT 1");
            $stmt->execute(['code' => trim($code)]);
            $id = $stmt->fetchColumn();
            if ($id) {
                return $this->findById((int)$id);
            }
        } catch (Throwable) {}

        return null;
    }

    public function getOrderItems(int $orderId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT oi.*, p.barcode, p.image_url, p.unit
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id
            ");
            $stmt->execute(['order_id' => $orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }

    public function getAll(array $filters = []): array
    {
        try {
            $sql = "
                SELECT o.*, 
                       u.first_name || ' ' || u.last_name as student_full_name,
                       u.phone as student_phone,
                       s.name as store_name
                FROM orders o
                LEFT JOIN users u ON o.student_id = u.id
                LEFT JOIN stores s ON o.store_id = s.id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filters['student_id'])) {
                $sql .= " AND o.student_id = :student_id";
                $params['student_id'] = (int)$filters['student_id'];
            }

            if (!empty($filters['store_id'])) {
                $sql .= " AND o.store_id = :store_id";
                $params['store_id'] = (int)$filters['store_id'];
            }

            if (!empty($filters['order_status'])) {
                $sql .= " AND o.order_status = :order_status";
                $params['order_status'] = $filters['order_status'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (o.order_no LIKE :search OR o.pickup_code LIKE :search2)";
                $params['search'] = '%' . $filters['search'] . '%';
                $params['search2'] = '%' . $filters['search'] . '%';
            }

            $sql .= " ORDER BY o.id DESC LIMIT :limit OFFSET :offset";
            
            $limit = min(100, max(1, (int)($filters['per_page'] ?? 30)));
            $page = max(1, (int)($filters['page'] ?? 1));
            $offset = ($page - 1) * $limit;

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }

    public function updateStatus(int $id, string $status, ?string $paymentStatus = null): bool
    {
        try {
            $sql = "UPDATE orders SET order_status = :status";
            $params = ['id' => $id, 'status' => $status];

            if ($paymentStatus !== null) {
                $sql .= ", payment_status = :payment_status";
                $params['payment_status'] = $paymentStatus;
            }

            $sql .= ", updated_at = :now WHERE id = :id";
            $params['now'] = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Throwable) {
            return false;
        }
    }
}
