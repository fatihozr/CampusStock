<?php

namespace App\Models;

use App\Services\MockDataService;

class Sale
{
    private const TABLE = 'sales';

    public function getAll(array $filters = []): array
    {
        $rows = array_reverse(MockDataService::all(self::TABLE));
        $filtered = [];

        foreach ($rows as $row) {
            if (!empty($filters['status']) && ($row['status'] ?? '') !== $filters['status']) {
                continue;
            }
            if (!empty($filters['payment_method']) && ($row['payment_method'] ?? '') !== $filters['payment_method']) {
                continue;
            }
            $filtered[] = $row;
        }

        if (!empty($filters['limit'])) {
            $filtered = array_slice($filtered, 0, (int)$filters['limit']);
        }
        return $filtered;
    }

    public function find(int $id): ?array
    {
        return MockDataService::find(self::TABLE, $id);
    }

    public function getDailySummary(?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $sales = MockDataService::all(self::TABLE);

        $count = 0;
        $total = 0.0;
        $cash = 0.0;
        $card = 0.0;

        foreach ($sales as $s) {
            if (str_starts_with($s['created_at'] ?? '', $date)) {
                $count++;
                $amt = (float)($s['grand_total'] ?? 0);
                $total += $amt;
                if (($s['payment_method'] ?? '') === 'cash') {
                    $cash += $amt;
                } else {
                    $card += $amt;
                }
            }
        }

        return [
            'date'              => $date,
            'total_sales_count' => $count,
            'total_revenue'     => $total,
            'cash_total'        => $cash,
            'card_total'        => $card
        ];
    }

    public function create(array $saleData, array $items): int
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $receiptNo = $saleData['receipt_no'] ?? ('KRT-' . date('Ymd') . '-' . str_pad((string)$newId, 4, '0', STR_PAD_LEFT));

        $formattedItems = [];
        foreach ($items as $idx => $item) {
            $formattedItems[] = [
                'id'                => ($newId * 100) + $idx + 1,
                'product_id'        => (int)$item['product_id'],
                'product_name'      => $item['product_name'] ?? 'Kırtasiye Ürünü',
                'quantity'          => (float)$item['quantity'],
                'unit_price'        => (float)$item['unit_price'],
                'line_total'        => (float)$item['line_total'],
                'returned_quantity' => 0.0
            ];
        }

        $rows[] = [
            'id'             => $newId,
            'receipt_no'     => $receiptNo,
            'customer_id'    => $saleData['customer_id'] ?? null,
            'customer_name'  => $saleData['customer_name'] ?? 'Peşin Kasa Müşterisi',
            'store_id'       => (int)($saleData['store_id'] ?? 1),
            'store_name'     => 'İTÜ Ayazağa Merkez Kırtasiye',
            'sale_type'      => $saleData['sale_type'] ?? 'pos',
            'payment_method' => $saleData['payment_method'] ?? 'cash',
            'payment_status' => 'Ödendi',
            'order_status'   => 'Teslim Edildi',
            'cargo_company'  => 'Mağaza Kasa',
            'cargo_tracking' => '-',
            'cargo_fee'      => 0.00,
            'subtotal'       => (float)($saleData['subtotal'] ?? 0),
            'tax_total'      => (float)($saleData['tax_total'] ?? 0),
            'discount_total' => (float)($saleData['discount_total'] ?? 0),
            'grand_total'    => (float)($saleData['grand_total'] ?? 0),
            'status'         => 'completed',
            'created_at'     => date('Y-m-d H:i:s'),
            'items'          => $formattedItems
        ];

        MockDataService::saveAll(self::TABLE, $rows);
        return $newId;
    }

    public function updateOrderStatus(int $id, string $orderStatus, ?string $cargoCompany = null, ?string $cargoTracking = null): bool
    {
        $rows = MockDataService::all(self::TABLE);
        foreach ($rows as &$row) {
            if ((int)$row['id'] === $id) {
                $row['order_status'] = $orderStatus;
                if ($cargoCompany !== null) $row['cargo_company'] = $cargoCompany;
                if ($cargoTracking !== null) $row['cargo_tracking'] = $cargoTracking;
                MockDataService::saveAll(self::TABLE, $rows);
                return true;
            }
        }
        return false;
    }
}
