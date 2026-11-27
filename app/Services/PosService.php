<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use Exception;

class PosService
{
    private Product $productModel;
    private Sale $saleModel;
    private Customer $customerModel;

    public function __construct()
    {
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
        $customerDiscountRate = $customer ? (float)($customer['discount_rate'] ?? 0.0) : 0.0;
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

            $lineRawTotal = $qty * $unitPrice;
            $lineDiscount = round($lineRawTotal * ($appliedDiscountRate / 100), 2);
            $lineNetTotal = $lineRawTotal - $lineDiscount;

            $taxRateDivisor = 1 + ($taxRate / 100);
            $lineWithoutTax = $lineNetTotal / $taxRateDivisor;
            $lineTax = round($lineNetTotal - $lineWithoutTax, 2);

            $subtotal += $lineRawTotal;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;

            $rateInt = (int)$taxRate;
            if (isset($taxBreakdown[$rateInt])) {
                $taxBreakdown[$rateInt] += $lineTax;
            } else {
                $taxBreakdown[$rateInt] = $lineTax;
            }

            $computedItems[] = [
                'product_id'      => (int)$product['id'],
                'barcode'         => $product['barcode'],
                'code'            => $product['code'],
                'name'            => $product['name'],
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
     * Complete a Sale (Mock-persisted Checkout)
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

        $receiptNo = 'KRT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $customerName = $cart['customer']
            ? trim(($cart['customer']['first_name'] ?? '') . ' ' . ($cart['customer']['last_name'] ?? ''))
            : 'Peşin Kasa Müşterisi';

        $saleItems = [];
        foreach ($cart['items'] as $item) {
            $saleItems[] = [
                'product_id'   => $item['product_id'],
                'product_name' => $item['name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'line_total'   => $item['line_total']
            ];

            $this->productModel->adjustStock(
                (int)$item['product_id'],
                -(float)$item['quantity'],
                'sale',
                'sale',
                $receiptNo,
                "Kırtasiye Satışı (Fiş No: {$receiptNo})",
                $userId
            );
        }

        $saleId = $this->saleModel->create([
            'receipt_no'     => $receiptNo,
            'customer_id'    => $customerId,
            'customer_name'  => $customerName,
            'sale_type'      => $saleType,
            'payment_method' => $paymentMethod,
            'subtotal'       => $cart['subtotal'],
            'tax_total'      => $cart['tax_total'],
            'discount_total' => $cart['discount_total'],
            'grand_total'    => $grandTotal
        ], $saleItems);

        if ($paymentMethod === 'open_account' && $customerId) {
            $this->customerModel->adjustBalance($customerId, $grandTotal);
        }

        return [
            'success'       => true,
            'sale_id'       => $saleId,
            'receipt_no'    => $receiptNo,
            'grand_total'   => $grandTotal,
            'paid_amount'   => $paidAmount,
            'change_amount' => $changeAmount,
            'receipt'       => $this->saleModel->find($saleId)
        ];
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

        $returnNo = 'IAD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $unitPrice = (float)$saleItem['unit_price'];
        $refundAmount = round($returnQuantity * $unitPrice, 2);

        $sales = MockDataService::all('sales');
        $allReturned = true;
        $anyReturned = false;
        foreach ($sales as &$s) {
            if ((int)$s['id'] === $saleId) {
                foreach ($s['items'] as &$si) {
                    if ((int)$si['id'] === $saleItemId) {
                        $si['returned_quantity'] = (float)$si['returned_quantity'] + $returnQuantity;
                    }
                    if ((float)$si['returned_quantity'] < (float)$si['quantity']) {
                        $allReturned = false;
                    }
                    if ((float)$si['returned_quantity'] > 0) {
                        $anyReturned = true;
                    }
                }
                $s['status'] = $allReturned ? 'refunded' : ($anyReturned ? 'partially_refunded' : 'completed');
                break;
            }
        }
        MockDataService::saveAll('sales', $sales);

        $returns = MockDataService::all('returns');
        $returns[] = [
            'id'            => MockDataService::nextId('returns'),
            'sale_id'       => $saleId,
            'receipt_no'    => $sale['receipt_no'],
            'product_name'  => $saleItem['product_name'],
            'customer_name' => $sale['customer_name'] ?? 'Müşteri',
            'reason'        => $reason,
            'return_amount' => $refundAmount,
            'status'        => 'Onaylandı',
            'created_at'    => date('Y-m-d H:i:s')
        ];
        MockDataService::saveAll('returns', $returns);

        $this->productModel->adjustStock(
            (int)$saleItem['product_id'],
            $returnQuantity,
            'sale_return',
            'return',
            $returnNo,
            "Satış İadesi ({$returnNo}): {$reason}",
            $userId
        );

        return [
            'success'       => true,
            'return_no'     => $returnNo,
            'refund_amount' => $refundAmount
        ];
    }
}
