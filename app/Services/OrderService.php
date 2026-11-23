<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Exception;

class OrderService
{
    private Order $orderModel;
    private Product $productModel;
    private StockMovement $stockMovementModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->stockMovementModel = new StockMovement();
    }

    /**
     * Create an online student order for campus pickup
     */
    public function createOrder(int $studentId, array $data): array
    {
        $items = $data['items'] ?? [];
        if (empty($items) || !is_array($items)) {
            throw new Exception("Siparişte en az bir ürün bulunmalıdır.");
        }

        $validatedItems = [];
        $totalAmount = 0.0;

        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $qty = max(1, (float)($item['quantity'] ?? 1));

            if ($productId <= 0) {
                continue;
            }

            $product = $this->productModel->find($productId);
            if (!$product) {
                throw new Exception("Ürün bulunamadı (ID: {$productId}).");
            }

            $unitPrice = (float)($product['sale_price'] ?? 0.0);
            $lineTotal = round($unitPrice * $qty, 2);
            $totalAmount += $lineTotal;

            $validatedItems[] = [
                'product_id'   => $productId,
                'product_name' => $product['name'],
                'quantity'     => $qty,
                'unit_price'   => $unitPrice,
                'line_total'   => $lineTotal
            ];
        }

        if (empty($validatedItems)) {
            throw new Exception("Geçerli ürün bulunamadı.");
        }

        $discountAmount = max(0.0, (float)($data['discount_amount'] ?? 0.0));
        $finalAmount = max(0.0, $totalAmount - $discountAmount);

        $orderPayload = [
            'student_id'      => $studentId,
            'store_id'        => (int)($data['store_id'] ?? 1),
            'total_amount'    => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount'    => $finalAmount,
            'payment_method'  => $data['payment_method'] ?? 'cash_pickup',
            'payment_status'  => ($data['payment_method'] ?? '') === 'online_card' ? 'paid' : 'pending',
            'notes'           => $data['notes'] ?? null
        ];

        $order = $this->orderModel->createOrder($orderPayload, $validatedItems);

        // Deduct stock for reserved items
        foreach ($validatedItems as $item) {
            try {
                $this->productModel->decreaseStock($item['product_id'], $item['quantity']);
            } catch (Exception) {}
        }

        return $order;
    }

    /**
     * Verify pickup code when student arrives at store
     */
    public function verifyPickup(string $code): ?array
    {
        $order = $this->orderModel->findByPickupCode($code);
        if (!$order) {
            return null;
        }

        return [
            'order'         => $order,
            'can_deliver'   => in_array($order['order_status'], ['received', 'preparing', 'ready']),
            'balance_to_pay'=> $order['payment_status'] === 'paid' ? 0.0 : (float)$order['final_amount']
        ];
    }

    /**
     * Mark order as delivered & update payment
     */
    public function deliverOrder(int $orderId, ?string $paymentMethod = null): array
    {
        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            throw new Exception("Sipariş bulunamadı.");
        }

        $this->orderModel->updateStatus($orderId, 'delivered', 'paid');
        return $this->orderModel->findById($orderId);
    }

    /**
     * Cancel order & restore stocks
     */
    public function cancelOrder(int $orderId): array
    {
        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            throw new Exception("Sipariş bulunamadı.");
        }

        if ($order['order_status'] === 'delivered') {
            throw new Exception("Teslim edilmiş sipariş iptal edilemez. Lütfen iade sürecini kullanın.");
        }

        // Restore stocks
        foreach ($order['items'] ?? [] as $item) {
            try {
                $this->productModel->increaseStock((int)$item['product_id'], (float)$item['quantity']);
            } catch (Exception) {}
        }

        $this->orderModel->updateStatus($orderId, 'cancelled');
        return $this->orderModel->findById($orderId);
    }
}
