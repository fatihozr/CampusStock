<?php

namespace App\Services;

use App\Config\Database;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\CashMovement;
use PDO;
use Exception;

class PosService
{
    private PDO $db;
    private Product $productModel;
    private Sale $saleModel;
    private Customer $customerModel;

    public function __construct()
    {
        $db = Database::getConnection();
        if (!$db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
        $this->db = $db;
        $this->productModel = new Product();
        $this->saleModel = new Sale();
        $this->customerModel = new Customer();
    }

    /**
     * Calculate cart totals, taxes, and customer discounts
     */
    public function calculateCart(array $rawItems, ?int $customerId = null, float $customDiscountRate = 0.0): array
    {
        $customer = $customerId ? $this->customerModel->find($customerId) : null;
        $customerDiscountRate = $customer ? (float)$customer['discount_rate'] : 0.0;
        $appliedDiscountRate = max($customerDiscountRate, $customDiscountRate);

        $computedItems = [];
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $discountTotal = 0.0;
        $taxBreakdown = [1 => 0.0, 10 => 0.0, 20 => 0.0];

        foreach ($rawItems as $item) {
            $product = null;
            if (!empty($item['product_id'])) {
                $product = $this->productModel->find((int)$item['product_id']);
            } elseif (!empty($item['barcode'])) {
                $product = $this->productModel->findByBarcode((string)$item['barcode']);
            }

            if (!$product) {
                continue;
            }

            $qty = max(1, (float)($item['quantity'] ?? 1));
            $unitPrice = isset($item['unit_price']) && is_numeric($item['unit_price']) 
                ? (float)$item['unit_price'] 
                : (float)$product['sale_price'];

            $costPrice = (float)$product['purchase_price'];
            $taxRate = (float)$product['tax_rate'];

            // Line discount calculation
            $lineRawTotal = $qty * $unitPrice;
            $lineDiscount = round($lineRawTotal * ($appliedDiscountRate / 100), 2);
            $lineNetTotal = $lineRawTotal - $lineDiscount;

            // Extract tax: Net total includes VAT (KDV Dahil)
            $taxRateDivisor = 1 + ($taxRate / 100);
            $lineWithoutTax = $lineNetTotal / $taxRateDivisor;
            $lineTax = round($lineNetTotal - $lineWithoutTax, 2);

            $subtotal += $lineRawTotal;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;

            $taxKey = (int)$taxRate;
            if (!isset($taxBreakdown[$taxKey])) {
                $taxBreakdown[$taxKey] = 0.0;
            }
            $taxBreakdown[$taxKey] += $lineTax;

            $computedItems[] = [
                'product_id'      => $product['id'],
                'code'            => $product['code'],
                'barcode'         => $product['barcode'],
                'name'            => $product['name'],
                'brand'           => $product['brand'],
                'unit'            => $product['unit'],
                'current_stock'   => (float)$product['current_stock'],
                'quantity'        => $qty,
                'unit_price'      => $unitPrice,
                'cost_price'      => $costPrice,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $lineTax,
                'discount_amount' => $lineDiscount,
                'line_total'      => $lineNetTotal
            ];
        }

        $grandTotal = round($subtotal - $discountTotal, 2);

        return [
            'customer'              => $customer,
            'applied_discount_rate' => $appliedDiscountRate,
            'items'                 => $computedItems,
            'subtotal'              => round($subtotal, 2),
            'tax_total'             => round($taxTotal, 2),
            'tax_breakdown'         => $taxBreakdown,
            'discount_total'        => round($discountTotal, 2),
            'grand_total'           => $grandTotal
        ];
    }

    /**
     * Complete a Sale (Transactional Checkout)
     */
    public function checkout(array $data, int $userId): array
    {
        $rawItems = $data['items'] ?? [];
        if (empty($rawItems)) {
            throw new Exception("Sepette ürün bulunmamaktadır.");
        }

        $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $customDiscountRate = (float)($data['discount_rate'] ?? 0.0);
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $saleType = $data['sale_type'] ?? 'pos';
        $notes = $data['notes'] ?? null;

        // Calculate cart
        $cart = $this->calculateCart($rawItems, $customerId, $customDiscountRate);
        if (empty($cart['items'])) {
            throw new Exception("Sepetteki ürünler geçerli değil.");
        }

        $grandTotal = $cart['grand_total'];
        $paidAmount = isset($data['paid_amount']) ? (float)$data['paid_amount'] : $grandTotal;
        $changeAmount = max(0.0, round($paidAmount - $grandTotal, 2));

        if ($paymentMethod === 'open_account' && !$customerId) {
            throw new Exception("Veresiye / Açık hesap satış yapabilmek için müşteri seçilmelidir.");
        }

        // Begin DB Transaction
        $this->db->beginTransaction();

        try {
            $receiptNo = $this->saleModel->generateReceiptNo();

            // 1. Insert into sales
            $saleStmt = $this->db->prepare("
                INSERT INTO sales (
                    receipt_no, customer_id, user_id, sale_type, payment_method,
                    subtotal, tax_total, discount_total, grand_total, paid_amount,
                    change_amount, status, notes
                ) VALUES (
                    :receipt_no, :customer_id, :user_id, :sale_type, :payment_method,
                    :subtotal, :tax_total, :discount_total, :grand_total, :paid_amount,
                    :change_amount, 'completed', :notes
                )
            ");

            $saleStmt->execute([
                'receipt_no'     => $receiptNo,
                'customer_id'    => $customerId,
                'user_id'        => $userId,
                'sale_type'      => $saleType,
                'payment_method' => $paymentMethod,
                'subtotal'       => $cart['subtotal'],
                'tax_total'      => $cart['tax_total'],
                'discount_total' => $cart['discount_total'],
                'grand_total'    => $grandTotal,
                'paid_amount'    => $paidAmount,
                'change_amount'  => $changeAmount,
                'notes'          => $notes
            ]);

            $saleId = (int)$this->db->lastInsertId();

            // 2. Insert items and decrement stock
            $itemStmt = $this->db->prepare("
                INSERT INTO sale_items (
                    sale_id, product_id, product_name, barcode, unit, quantity,
                    unit_price, cost_price, tax_rate, tax_amount, discount_amount, line_total
                ) VALUES (
                    :sale_id, :product_id, :product_name, :barcode, :unit, :quantity,
                    :unit_price, :cost_price, :tax_rate, :tax_amount, :discount_amount, :line_total
                )
            ");

            $movementModel = new StockMovement();

            foreach ($cart['items'] as $item) {
                $itemStmt->execute([
                    'sale_id'         => $saleId,
                    'product_id'      => $item['product_id'],
                    'product_name'    => $item['name'],
                    'barcode'         => $item['barcode'],
                    'unit'            => $item['unit'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'cost_price'      => $item['cost_price'],
                    'tax_rate'        => $item['tax_rate'],
                    'tax_amount'      => $item['tax_amount'],
                    'discount_amount' => $item['discount_amount'],
                    'line_total'      => $item['line_total']
                ]);

                // Atomically update stock
                $currentStock = (float)$item['current_stock'];
                $newStock = $currentStock - (float)$item['quantity'];

                $stockStmt = $this->db->prepare("UPDATE products SET current_stock = :new_stock WHERE id = :id");
                $stockStmt->execute([
                    'new_stock' => $newStock,
                    'id'        => $item['product_id']
                ]);

                // Record stock movement
                $movementModel->record([
                    'product_id'     => $item['product_id'],
                    'movement_type'  => 'sale',
                    'quantity'       => -(float)$item['quantity'],
                    'prev_stock'     => $currentStock,
                    'next_stock'     => $newStock,
                    'unit_cost'      => (float)$item['cost_price'],
                    'total_cost'     => (float)$item['quantity'] * (float)$item['cost_price'],
                    'reference_type' => 'sale',
                    'reference_id'   => $receiptNo,
                    'notes'          => "Kırtasiye Satışı (Fiş No: {$receiptNo})",
                    'created_by'     => $userId
                ]);
            }

            // 3. Customer balance if open_account
            if ($paymentMethod === 'open_account' && $customerId) {
                $this->customerModel->adjustBalance($customerId, $grandTotal);
            }

            // 4. Cash movement logging
            if (in_array($paymentMethod, ['cash', 'credit_card'])) {
                $cashModel = new CashMovement();
                $cashModel->record([
                    'user_id'        => $userId,
                    'movement_type'  => $paymentMethod === 'cash' ? 'sale_cash' : 'sale_card',
                    'amount'         => $grandTotal,
                    'payment_method' => $paymentMethod,
                    'reference_id'   => $receiptNo,
                    'description'    => "POS Satışı Fiş No: {$receiptNo}"
                ]);
            }

            $this->db->commit();

            return [
                'success'       => true,
                'sale_id'       => $saleId,
                'receipt_no'    => $receiptNo,
                'grand_total'   => $grandTotal,
                'paid_amount'   => $paidAmount,
                'change_amount' => $changeAmount,
                'receipt'       => $this->saleModel->find($saleId)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Satış işlemi kaydedilemedi: " . $e->getMessage());
        }
    }

    /**
     * Process return of an item
     */
    public function processReturn(int $saleId, int $saleItemId, float $returnQuantity, string $reason, int $userId): array
    {
        $sale = $this->saleModel->find($saleId);
        if (!$sale) {
            throw new Exception("Satış kaydı bulunamadı.");
        }

        $saleItem = null;
        foreach ($sale['items'] as $item) {
            if ((int)$item['id'] === $saleItemId) {
                $saleItem = $item;
                break;
            }
        }

        if (!$saleItem) {
            throw new Exception("Satış kalemi bulunamadı.");
        }

        $availableToReturn = (float)$saleItem['quantity'] - (float)$saleItem['returned_quantity'];
        if ($returnQuantity <= 0 || $returnQuantity > $availableToReturn) {
            throw new Exception("İade miktarı geçersiz. İade edilebilir maksimum adet: {$availableToReturn}");
        }

        $this->db->beginTransaction();

        try {
            $returnNo = 'IAD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $unitPrice = (float)$saleItem['unit_price'];
            $refundAmount = round($returnQuantity * $unitPrice, 2);

            // 1. Insert return
            $retStmt = $this->db->prepare("
                INSERT INTO sale_returns (
                    return_no, sale_id, sale_item_id, product_id, quantity,
                    unit_price, total_refund, reason, refund_method, processed_by
                ) VALUES (
                    :return_no, :sale_id, :sale_item_id, :product_id, :quantity,
                    :unit_price, :total_refund, :reason, 'cash', :processed_by
                )
            ");
            $retStmt->execute([
                'return_no'    => $returnNo,
                'sale_id'      => $saleId,
                'sale_item_id' => $saleItemId,
                'product_id'   => $saleItem['product_id'],
                'quantity'     => $returnQuantity,
                'unit_price'   => $unitPrice,
                'total_refund' => $refundAmount,
                'reason'       => $reason,
                'processed_by' => $userId
            ]);

            // 2. Update returned_quantity on sale_item
            $updItem = $this->db->prepare("
                UPDATE sale_items 
                SET returned_quantity = returned_quantity + :qty 
                WHERE id = :id
            ");
            $updItem->execute(['qty' => $returnQuantity, 'id' => $saleItemId]);

            // 3. Restore product stock
            $prod = $this->productModel->find((int)$saleItem['product_id']);
            $prevStock = (float)$prod['current_stock'];
            $nextStock = $prevStock + $returnQuantity;

            $prodUpd = $this->db->prepare("UPDATE products SET current_stock = :next_stock WHERE id = :id");
            $prodUpd->execute(['next_stock' => $nextStock, 'id' => $saleItem['product_id']]);

            // 4. Record stock movement
            $movementModel = new StockMovement();
            $movementModel->record([
                'product_id'     => $saleItem['product_id'],
                'movement_type'  => 'sale_return',
                'quantity'       => $returnQuantity,
                'prev_stock'     => $prevStock,
                'next_stock'     => $nextStock,
                'unit_cost'      => (float)$saleItem['cost_price'],
                'total_cost'     => $returnQuantity * (float)$saleItem['cost_price'],
                'reference_type' => 'return',
                'reference_id'   => $returnNo,
                'notes'          => "Müşteri İadesi (Fiş: {$sale['receipt_no']}, Neden: {$reason})",
                'created_by'     => $userId
            ]);

            // 5. Update Sale status if fully returned
            $chkItems = $this->db->prepare("SELECT quantity, returned_quantity FROM sale_items WHERE sale_id = :sale_id");
            $chkItems->execute(['sale_id' => $saleId]);
            $allItems = $chkItems->fetchAll(PDO::FETCH_ASSOC);

            $allReturned = true;
            $anyReturned = false;
            foreach ($allItems as $it) {
                if ((float)$it['returned_quantity'] < (float)$it['quantity']) {
                    $allReturned = false;
                }
                if ((float)$it['returned_quantity'] > 0) {
                    $anyReturned = true;
                }
            }

            $newStatus = $allReturned ? 'refunded' : ($anyReturned ? 'partially_refunded' : 'completed');
            $updSale = $this->db->prepare("UPDATE sales SET status = :status WHERE id = :id");
            $updSale->execute(['status' => $newStatus, 'id' => $saleId]);

            // 6. Cash movement refund
            $cashModel = new CashMovement();
            $cashModel->record([
                'user_id'        => $userId,
                'movement_type'  => 'refund_cash',
                'amount'         => -$refundAmount,
                'payment_method' => 'cash',
                'reference_id'   => $returnNo,
                'description'    => "Satış İadesi ({$sale['receipt_no']}) - {$reason}"
            ]);

            $this->db->commit();

            return [
                'success'       => true,
                'return_no'     => $returnNo,
                'refund_amount' => $refundAmount,
                'new_status'    => $newStatus
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("İade işlemi tamamlanamadı: " . $e->getMessage());
        }
    }
}
