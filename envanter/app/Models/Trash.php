<?php

namespace App\Models;

use App\Helpers\Auth;
use Exception;

class Trash
{
    public function __construct()
    {
        $this->initSessionData();
    }

    /**
     * Initialize trash items if not set
     */
    private function initSessionData(): void
    {
        if (!isset($_SESSION['mock_trash'])) {
            $_SESSION['mock_trash'] = [
                [
                    'id'           => 1,
                    'type'         => 'stock',
                    'type_label'   => 'Stok Kalemi',
                    'item_id'      => 99,
                    'item_code'    => 'STK-OLD-01',
                    'item_name'    => 'Temizlik Malzemesi (Test)',
                    'deleted_at'   => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'deleted_by'   => 'Ayşe Kaya (Depo Sorumlusu)',
                    'data'         => [
                        'id'            => 99,
                        'code'          => 'STK-OLD-01',
                        'name'          => 'Temizlik Malzemesi (Test)',
                        'category'      => 'Sarf Malzeme',
                        'unit'          => 'Adet',
                        'quantity'      => 5,
                        'min_threshold' => 2,
                        'warehouse'     => 'Merkez Ambar',
                        'rack_location' => 'C31',
                        'unit_price'    => '250.00'
                    ]
                ]
            ];
        }
    }

    /**
     * Get all soft-deleted trash items
     */
    public function getAll(): array
    {
        return array_values($_SESSION['mock_trash'] ?? []);
    }

    /**
     * Move an item to trash (Soft Delete)
     */
    public function softDelete(string $type, int $itemId, string $code, string $name, array $data): array
    {
        $user = Auth::user();
        $username = $user['name'] ?? $user['username'] ?? 'Sistem Yöneticisi';

        $newId = count($_SESSION['mock_trash']) > 0
            ? max(array_column($_SESSION['mock_trash'], 'id')) + 1
            : 1;

        $typeLabels = [
            'stock'     => 'Stok Kalemi',
            'personnel' => 'Personel Kaydı',
            'warehouse' => 'Depo Kaydı'
        ];

        $trashEntry = [
            'id'         => $newId,
            'type'       => $type,
            'type_label' => $typeLabels[$type] ?? ucfirst($type),
            'item_id'    => $itemId,
            'item_code'  => $code,
            'item_name'  => $name,
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $username,
            'data'       => $data
        ];

        array_unshift($_SESSION['mock_trash'], $trashEntry);

        if (function_exists('log_audit')) {
            log_audit('Çöp Kutusuna Gönderildi', 'Çöp Kutusu', "[$code] $name kaydı çöp kutusuna taşındı.");
        }

        return $trashEntry;
    }

    /**
     * Restore item from trash back to active list
     */
    public function restore(int $trashId): bool
    {
        $foundIdx = -1;
        $entry = null;

        foreach ($_SESSION['mock_trash'] as $idx => $t) {
            if ($t['id'] === $trashId) {
                $foundIdx = $idx;
                $entry = $t;
                break;
            }
        }

        if ($foundIdx === -1 || !$entry) {
            throw new Exception('Çöp kutusunda istenen kayıt bulunamadı!');
        }

        $type = $entry['type'];
        $data = $entry['data'];

        // Restore to target model
        if ($type === 'stock') {
            $stockModel = new Stock();
            $existing = $stockModel->findByCode($data['code']);
            if ($existing && $existing['id'] !== $data['id']) {
                throw new Exception('Geri yüklenmek istenen [' . $data['code'] . '] stok kodu zaten aktif stok listesinde mevcut!');
            }
            $stockModel->store($data);
        } elseif ($type === 'personnel') {
            if (!isset($_SESSION['mock_personnel'])) $_SESSION['mock_personnel'] = [];
            $_SESSION['mock_personnel'][] = $data;
        }

        // Remove from trash
        unset($_SESSION['mock_trash'][$foundIdx]);
        $_SESSION['mock_trash'] = array_values($_SESSION['mock_trash']);

        if (function_exists('log_audit')) {
            log_audit('Geri Yüklendi', 'Çöp Kutusu', "[{$entry['item_code']}] {$entry['item_name']} kaydı aktif listeye geri yüklendi.");
        }

        return true;
    }

    /**
     * Permanently purge item from trash (Admin only)
     */
    public function purge(int $trashId): bool
    {
        $foundIdx = -1;
        $entry = null;

        foreach ($_SESSION['mock_trash'] as $idx => $t) {
            if ($t['id'] === $trashId) {
                $foundIdx = $idx;
                $entry = $t;
                break;
            }
        }

        if ($foundIdx === -1 || !$entry) {
            throw new Exception('Çöp kutusunda kalıcı olarak silinecek kayıt bulunamadı!');
        }

        unset($_SESSION['mock_trash'][$foundIdx]);
        $_SESSION['mock_trash'] = array_values($_SESSION['mock_trash']);

        if (function_exists('log_audit')) {
            log_audit('Kalıcı Olarak Silindi', 'Çöp Kutusu', "[{$entry['item_code']}] {$entry['item_name']} veritabanından kalıcı olarak silindi.");
        }

        return true;
    }
}
