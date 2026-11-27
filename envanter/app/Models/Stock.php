<?php

namespace App\Models;

use App\Helpers\Auth;
use Exception;

class Stock
{
    private Warehouse $warehouseModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->warehouseModel = new Warehouse();
        $this->categoryModel  = new Category();
        $this->initMockSessionData();
    }

    /**
     * Initialize mock cleaning product dataset and movement history
     */
    private function initMockSessionData(): void
    {
        if (!isset($_SESSION['mock_stocks']) || count($_SESSION['mock_stocks']) < 5 || isset($_SESSION['mock_stocks'][0]['code']) && str_starts_with($_SESSION['mock_stocks'][0]['code'], 'STK-')) {
            $_SESSION['mock_stocks'] = [
                [
                    'id'            => 1,
                    'code'          => 'TMZ-00001',
                    'barcode'       => '8690000000011',
                    'name'          => 'Çamaşır Suyu 5 L (Ultra Yoğun)',
                    'category'      => 'Temizlik Kimyasalları',
                    'category_id'   => 1,
                    'unit'          => 'Bidon',
                    'quantity'      => 80,
                    'min_threshold' => 20,
                    'unit_price'    => 145.50,
                    'brand'         => 'Domestos / Pro',
                    'specifications'=> '5 Litre kilitli kapak ambalaj',
                    'description'   => 'Genel tuvalet ve zemin dezenfeksiyonu için klor bazlı çamaşır suyu.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'A-101',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-30 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 2,
                    'code'          => 'TMZ-00002',
                    'barcode'       => '8690000000028',
                    'name'          => 'Sıvı Sabun 5 L (Antibakteriyel)',
                    'category'      => 'Temizlik Kimyasalları',
                    'category_id'   => 1,
                    'unit'          => 'Bidon',
                    'quantity'      => 8,
                    'min_threshold' => 10,
                    'unit_price'    => 120.00,
                    'brand'         => 'Duru / Pro',
                    'specifications'=> '5 Litre gliserinli cilt dostu formül',
                    'description'   => 'Personel lavaboları ve dispenseri doldurma sıvı sabun.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'A-102',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-25 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 3,
                    'code'          => 'TMZ-00003',
                    'barcode'       => '8690000000035',
                    'name'          => 'Tuvalet Kağıdı 24\'lü Roll (Çift Katlı)',
                    'category'      => 'Kağıt Ürünleri',
                    'category_id'   => 2,
                    'unit'          => 'Paket',
                    'quantity'      => 120,
                    'min_threshold' => 30,
                    'unit_price'    => 185.00,
                    'brand'         => 'Selpak / Selpak Pro',
                    'specifications'=> '%100 Selüloz 2 katlı 24\'lü rulo',
                    'description'   => 'Hizmet binaları wc alanları için yüksek emicilikli tuvalet kağıdı.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'B-201',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-20 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 4,
                    'code'          => 'TMZ-00004',
                    'barcode'       => '8690000000042',
                    'name'          => 'Z Katlama Kağıt Havlu (200\'lü 12 Paket Koli)',
                    'category'      => 'Kağıt Ürünleri',
                    'category_id'   => 2,
                    'unit'          => 'Koli',
                    'quantity'      => 45,
                    'min_threshold' => 15,
                    'unit_price'    => 340.00,
                    'brand'         => 'Marathon / Dispens',
                    'specifications'=> 'Dispenser uyumlu Z katlama 2 kat selüloz',
                    'description'   => 'Lavabo havluluk dispenserleri için Z katlamalı kağıt havlu.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'B-202',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-18 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 5,
                    'code'          => 'TMZ-00005',
                    'barcode'       => '8690000000059',
                    'name'          => 'Çöp Poşeti 80x110 Battal Boy (10\'lu Rulo 20 Paket)',
                    'category'      => 'Çöp ve Atık Grubu',
                    'category_id'   => 4,
                    'unit'          => 'Koli',
                    'quantity'      => 0,
                    'min_threshold' => 10,
                    'unit_price'    => 260.00,
                    'brand'         => 'Koroplast / Sanayi',
                    'specifications'=> 'Siyah sızdırmaz mukavemetli LDPE 80x110 cm',
                    'description'   => 'Kat çöp kovaları ve dış mekan büyük atık üniteleri için.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'C-301',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-15 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 6,
                    'code'          => 'TMZ-00006',
                    'barcode'       => '8690000000066',
                    'name'          => 'Genel Yüzey Temizleyici 5 L (Parfümlü)',
                    'category'      => 'Temizlik Kimyasalları',
                    'category_id'   => 1,
                    'unit'          => 'Bidon',
                    'quantity'      => 25,
                    'min_threshold' => 5,
                    'unit_price'    => 115.00,
                    'brand'         => 'Cif / Pro',
                    'specifications'=> 'Konsantre zemin yıkama sıvısı Çam kokulu',
                    'description'   => 'Mermer, karo ve marley zemin genel temizlik ilacı.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'A-103',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-12 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 7,
                    'code'          => 'TMZ-00007',
                    'barcode'       => '8690000000073',
                    'name'          => 'Alkollü Yüzey Dezenfektanı 1 L (Püskürtmeli)',
                    'category'      => 'Temizlik Kimyasalları',
                    'category_id'   => 1,
                    'unit'          => 'Adet',
                    'quantity'      => 50,
                    'min_threshold' => 15,
                    'unit_price'    => 65.00,
                    'brand'         => 'Baktolin / Med',
                    'specifications'=> '%70 İzopropil alkol içerikli sprey',
                    'description'   => 'Masa, kapı kolu ve ortak temas yüzeyi dezenfektanı.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'A-104',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-10 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 8,
                    'code'          => 'TMZ-00008',
                    'barcode'       => '8690000000080',
                    'name'          => 'Mikrofiber Temizlik Bezi 40x40 (4\'lü Paket)',
                    'category'      => 'Temizlik Ekipmanları',
                    'category_id'   => 3,
                    'unit'          => 'Paket',
                    'quantity'      => 35,
                    'min_threshold' => 10,
                    'unit_price'    => 85.00,
                    'brand'         => 'Vileda / Pro',
                    'specifications'=> '4 renk kodlu (Mavi, Kırmızı, Sarı, Yeşil) iz bırakmaz',
                    'description'   => 'Büro mobilyası ve cam yüzey silme mikrofiber bez takımı.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'D-401',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-8 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 9,
                    'code'          => 'TMZ-00009',
                    'barcode'       => '8690000000097',
                    'name'          => 'Islak Mop Paspas Yedek Başlık (Pamuklu 350 gr)',
                    'category'      => 'Temizlik Ekipmanları',
                    'category_id'   => 3,
                    'unit'          => 'Adet',
                    'quantity'      => 12,
                    'min_threshold' => 5,
                    'unit_price'    => 55.00,
                    'brand'         => 'Vileda / Orjinal',
                    'specifications'=> 'Vidalı aparat uyumlu emici pamuk mop',
                    'description'   => 'Kat temizlik arabaları nemli ve ıslak paspas başlığı.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'D-402',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-5 days')),
                    'deleted_at'    => null
                ],
                [
                    'id'            => 10,
                    'code'          => 'TMZ-00010',
                    'barcode'       => '8690000000103',
                    'name'          => 'Bulaşık Deterjanı 5 L (Limon Kokulu)',
                    'category'      => 'Temizlik Kimyasalları',
                    'category_id'   => 1,
                    'unit'          => 'Bidon',
                    'quantity'      => 18,
                    'min_threshold' => 5,
                    'unit_price'    => 130.00,
                    'brand'         => 'Fairy / Pro',
                    'specifications'=> '5 Litre yüksek yağ çözücülü formül',
                    'description'   => 'Personel çay ocakları ve yemekhane lavabo bulaşık deterjanı.',
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'warehouse_id'  => 1,
                    'rack_location' => 'A-105',
                    'status'        => 'Aktif',
                    'image_path'    => null,
                    'created_at'    => date('Y-m-d H:i:s', strtotime('-3 days')),
                    'deleted_at'    => null
                ]
            ];
        }

        if (!isset($_SESSION['mock_stock_movements']) || count($_SESSION['mock_stock_movements']) < 2 || isset($_SESSION['mock_stock_movements'][0]['document_no']) && str_starts_with($_SESSION['mock_stock_movements'][0]['document_no'], 'STR-')) {
            $_SESSION['mock_stock_movements'] = [
                [
                    'id'            => 1,
                    'document_no'   => 'EVR-2026-0001',
                    'stock_id'      => 1,
                    'product_code'  => 'TMZ-00001',
                    'item_name'     => 'Çamaşır Suyu 5 L (Ultra Yoğun)',
                    'category'      => 'Temizlik Kimyasalları',
                    'movement_type' => 'STOK_GIRIS',
                    'movement_label'=> 'Stok Girişi',
                    'quantity'      => 100,
                    'prev_stock'    => 0,
                    'next_stock'    => 100,
                    'unit_cost'     => 145.50,
                    'total_cost'    => 14550.00,
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'performed_by'  => 'Ayşe Kaya (Depo Sorumlusu)',
                    'date'          => date('Y-m-d H:i:s', strtotime('-15 days')),
                    'description'   => 'İhale alım tedariği ambara ilk stok girişi.'
                ],
                [
                    'id'            => 2,
                    'document_no'   => 'EVR-2026-0002',
                    'stock_id'      => 1,
                    'product_code'  => 'TMZ-00001',
                    'item_name'     => 'Çamaşır Suyu 5 L (Ultra Yoğun)',
                    'category'      => 'Temizlik Kimyasalları',
                    'movement_type' => 'STOK_CIKIS',
                    'movement_label'=> 'Stok Çıkışı',
                    'quantity'      => 20,
                    'prev_stock'    => 100,
                    'next_stock'    => 80,
                    'unit_cost'     => 145.50,
                    'total_cost'    => 2910.00,
                    'warehouse'     => 'Destek Hizmetleri Ana Deposu',
                    'performed_by'  => 'Sistem Yöneticisi',
                    'date'          => date('Y-m-d H:i:s', strtotime('-5 days')),
                    'description'   => 'Hizmet binaları aylık periyodik temizlik malzemesi çıkışı.'
                ]
            ];
        }
    }

    /**
     * Get list of standard unit choices for cleaning materials
     */
    public static function getUnitChoices(): array
    {
        return [
            'Adet'   => 'Adet (Tekli Ürün)',
            'Bidon'  => 'Bidon (5L - 20L Sıvı)',
            'Koli'   => 'Koli (Toplu Ambalaj)',
            'Paket'  => 'Paket (Sarf Paketi)',
            'Rulo'   => 'Rulo (Kağıt / Çöp Grubu)',
            'Çift'   => 'Çift (Eldiven vb.)',
            'Makara' => 'Makara / Rulo',
            'Litre'  => 'Litre (Dökme Sıvı)',
            'Kg'     => 'Kilogram (Dökme Toz)'
        ];
    }

    /**
     * Check if product code already exists in active items (excluding current ID on edit)
     */
    public function checkDuplicateCode(string $code, ?int $excludeId = null): bool
    {
        $code = mb_strtolower(trim($code), 'UTF-8');
        if (empty($code)) return false;

        foreach ($_SESSION['mock_stocks'] as $item) {
            if (!empty($item['deleted_at'])) continue;
            if ($excludeId !== null && (int)$item['id'] === $excludeId) continue;
            if (mb_strtolower(trim($item['code']), 'UTF-8') === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if barcode already exists in active items (excluding current ID on edit)
     */
    public function checkDuplicateBarcode(string $barcode, ?int $excludeId = null): bool
    {
        $barcode = mb_strtolower(trim($barcode), 'UTF-8');
        if (empty($barcode)) return false;

        foreach ($_SESSION['mock_stocks'] as $item) {
            if (!empty($item['deleted_at'])) continue;
            if ($excludeId !== null && (int)$item['id'] === $excludeId) continue;
            if (!empty($item['barcode']) && mb_strtolower(trim($item['barcode']), 'UTF-8') === $barcode) {
                return true;
            }
        }
        return false;
    }

    /**
     * Secure Image Upload Handler for Cleaning Products
     */
    public function saveProductImage(array $file): string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Görsel yüklenirken bir hata oluştu (Kod: ' . ($file['error'] ?? 'UNKNOWN') . ')');
        }

        // Max File Size Check (5 MB)
        $maxSizeBytes = 5 * 1024 * 1024;
        if ($file['size'] > $maxSizeBytes) {
            throw new Exception('Yüklenen görsel dosyası boyutu maksimum 5 MB olabilir!');
        }

        // Extension check
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new Exception('Geçersiz dosya uzantısı! Yalnızca JPG, JPEG, PNG ve WEBP formatları desteklenmektedir.');
        }

        // MIME Type Check
        $mimeType = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        } else {
            $mimeType = $file['type'] ?? '';
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/x-png', 'image/pjpeg'];
        if (!empty($mimeType) && !in_array(strtolower($mimeType), $allowedMimes, true)) {
            throw new Exception("Güvenlik Uyarısı: Geçersiz dosya içeriği/MIME türü ('{$mimeType}')!");
        }

        // Secure Random Filename Generation under public/uploads/products/
        $uploadDir = BASE_PATH . '/public/uploads/products';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $randomName = 'prd_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $targetPath = $uploadDir . '/' . $randomName;

        $saved = false;
        if (is_uploaded_file($file['tmp_name'])) {
            $saved = move_uploaded_file($file['tmp_name'], $targetPath);
        } else {
            $saved = copy($file['tmp_name'], $targetPath);
        }

        if (!$saved) {
            throw new Exception('Görsel hedef dizine kaydedilemedi!');
        }

        return 'uploads/products/' . $randomName;
    }

    /**
     * Get all active (non soft-deleted) cleaning product items
     */
    public function getAllItems(): array
    {
        $items = [];
        foreach ($_SESSION['mock_stocks'] as $item) {
            if (!empty($item['deleted_at']) || ($item['status'] ?? '') === 'Silindi') {
                continue;
            }
            $item['quantity']      = (int)($item['quantity'] ?? 0);
            $item['min_threshold'] = (int)($item['min_threshold'] ?? 0);
            $item['unit_price']    = (float)($item['unit_price'] ?? 0);
            $item['total_val']     = $item['quantity'] * $item['unit_price'];

            // Stock Status Logic
            if ($item['quantity'] === 0) {
                $item['stock_status_code']  = 'OUT_OF_STOCK';
                $item['stock_status_label'] = 'STOK YOK';
                $item['badge_class']        = 'bg-danger text-white';
            } elseif ($item['quantity'] <= $item['min_threshold']) {
                $item['stock_status_code']  = 'CRITICAL';
                $item['stock_status_label'] = 'KRİTİK STOK';
                $item['badge_class']        = 'bg-warning text-dark';
            } else {
                $item['stock_status_code']  = 'NORMAL';
                $item['stock_status_label'] = 'NORMAL';
                $item['badge_class']        = 'bg-success text-white';
            }

            $item['missing_qty'] = max(0, $item['min_threshold'] - $item['quantity']);
            $item['is_critical'] = ($item['quantity'] <= $item['min_threshold']);

            $items[] = $item;
        }

        // Turkish multi-key sorting: Category ASC -> Name ASC -> Code ASC
        usort($items, function ($a, $b) {
            $cmpCat = turkish_strcmp($a['category'] ?? '', $b['category'] ?? '');
            if ($cmpCat !== 0) return $cmpCat;
            $cmpName = turkish_strcmp($a['name'] ?? '', $b['name'] ?? '');
            if ($cmpName !== 0) return $cmpName;
            return strnatcasecmp($a['code'] ?? '', $b['code'] ?? '');
        });

        return $items;
    }

    /**
     * Get filtered & paginated cleaning products list
     */
    public function getFilteredPaginated(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $items = $this->getAllItems();

        // Search query filter (search in name, code, barcode, brand)
        if (!empty($filters['search'])) {
            $search = mb_strtolower(trim($filters['search']), 'UTF-8');
            $items = array_filter($items, function ($i) use ($search) {
                return str_contains(mb_strtolower($i['name'] ?? '', 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($i['code'] ?? '', 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($i['barcode'] ?? '', 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($i['brand'] ?? '', 'UTF-8'), $search);
            });
        }

        // Category filter
        if (!empty($filters['category'])) {
            $cat = trim($filters['category']);
            $items = array_filter($items, fn($i) => ($i['category'] ?? '') === $cat || (string)($i['category_id'] ?? '') === $cat);
        }

        // Status tab filter: 'all', 'critical', 'out_of_stock', 'active', 'passive'
        if (!empty($filters['status_tab'])) {
            $st = strtolower(trim($filters['status_tab']));
            if ($st === 'critical') {
                $items = array_filter($items, fn($i) => $i['stock_status_code'] === 'CRITICAL');
            } elseif ($st === 'out_of_stock') {
                $items = array_filter($items, fn($i) => $i['stock_status_code'] === 'OUT_OF_STOCK');
            } elseif ($st === 'active') {
                $items = array_filter($items, fn($i) => ($i['status'] ?? 'Aktif') === 'Aktif');
            } elseif ($st === 'passive') {
                $items = array_filter($items, fn($i) => ($i['status'] ?? '') === 'Pasif');
            }
        }

        $totalItems = count($items);
        $totalPages = (int)ceil($totalItems / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($page > $totalPages) $page = $totalPages;

        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice(array_values($items), $offset, $perPage);

        return [
            'data'        => $paginatedItems,
            'total_items' => $totalItems,
            'current_page'=> $page,
            'total_pages' => $totalPages,
            'per_page'    => $perPage
        ];
    }

    /**
     * Get Critical Stock Items Count
     */
    public function getCriticalStockCount(): int
    {
        $count = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item['is_critical']) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Find single cleaning product by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_stocks'] as $item) {
            if ((int)$item['id'] === $id) {
                $item['quantity']      = (int)($item['quantity'] ?? 0);
                $item['min_threshold'] = (int)($item['min_threshold'] ?? 0);
                $item['unit_price']    = (float)($item['unit_price'] ?? 0);
                $item['total_val']     = $item['quantity'] * $item['unit_price'];

                if ($item['quantity'] === 0) {
                    $item['stock_status_code']  = 'OUT_OF_STOCK';
                    $item['stock_status_label'] = 'STOK YOK';
                    $item['badge_class']        = 'bg-danger text-white';
                } elseif ($item['quantity'] <= $item['min_threshold']) {
                    $item['stock_status_code']  = 'CRITICAL';
                    $item['stock_status_label'] = 'KRİTİK STOK';
                    $item['badge_class']        = 'bg-warning text-dark';
                } else {
                    $item['stock_status_code']  = 'NORMAL';
                    $item['stock_status_label'] = 'NORMAL';
                    $item['badge_class']        = 'bg-success text-white';
                }

                $item['missing_qty'] = max(0, $item['min_threshold'] - $item['quantity']);
                $item['is_critical'] = ($item['quantity'] <= $item['min_threshold']);

                return $item;
            }
        }
        return null;
    }

    /**
     * Find cleaning product by Product Code
     */
    public function findByCode(string $code): ?array
    {
        $codeLower = mb_strtolower(trim($code), 'UTF-8');
        foreach ($_SESSION['mock_stocks'] as $item) {
            if (mb_strtolower(trim($item['code'] ?? ''), 'UTF-8') === $codeLower) {
                return $this->find((int)$item['id']);
            }
        }
        return null;
    }

    /**
     * Create or Store new Cleaning Product
     */
    public function store(array $data): array
    {
        $code = trim($data['code'] ?? '');
        $name = trim($data['name'] ?? '');

        if (empty($code) || empty($name)) {
            throw new Exception('Ürün Kodu ve Malzeme Adı alanları zorunludur!');
        }

        if ($this->checkDuplicateCode($code)) {
            throw new Exception("['{$code}'] ürün kodu sistemde zaten kayıtlıdır!");
        }

        $barcode = trim($data['barcode'] ?? '');
        if (!empty($barcode) && $this->checkDuplicateBarcode($barcode)) {
            throw new Exception("['{$barcode}'] barkod numarası başka bir üründe tanımlıdır!");
        }

        $newId = count($_SESSION['mock_stocks']) > 0
            ? max(array_column($_SESSION['mock_stocks'], 'id')) + 1
            : 1;

        $newProduct = [
            'id'            => $newId,
            'code'          => strtoupper($code),
            'barcode'       => $barcode ?: null,
            'name'          => $name,
            'category'      => trim($data['category'] ?? 'Temizlik Kimyasalları'),
            'category_id'   => (int)($data['category_id'] ?? 1),
            'unit'          => trim($data['unit'] ?? 'Adet'),
            'quantity'      => max(0, (int)($data['quantity'] ?? 0)),
            'min_threshold' => max(0, (int)($data['min_threshold'] ?? 5)),
            'unit_price'    => max(0.0, (float)($data['unit_price'] ?? 0.0)),
            'brand'         => trim($data['brand'] ?? ''),
            'specifications'=> trim($data['specifications'] ?? ''),
            'description'   => trim($data['description'] ?? ''),
            'warehouse'     => trim($data['warehouse'] ?? 'Destek Hizmetleri Ana Deposu'),
            'warehouse_id'  => (int)($data['warehouse_id'] ?? 1),
            'rack_location' => trim($data['rack_location'] ?? 'A-101'),
            'status'        => trim($data['status'] ?? 'Aktif'),
            'image_path'    => $data['image_path'] ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
            'deleted_at'    => null
        ];

        $_SESSION['mock_stocks'][] = $newProduct;

        if (function_exists('log_audit')) {
            log_audit('Ürün Eklendi', 'Ürün Kataloğu', "[{$code}] {$name} yeni temizlik malzemesi kataloğa eklendi.");
        }

        return $newProduct;
    }

    /**
     * Update existing Cleaning Product
     */
    public function update(int $id, array $data): bool
    {
        $foundIdx = -1;
        foreach ($_SESSION['mock_stocks'] as $idx => $item) {
            if ((int)$item['id'] === $id) {
                $foundIdx = $idx;
                break;
            }
        }

        if ($foundIdx === -1) {
            throw new Exception('Güncellenecek temizlik malzemesi bulunamadı!');
        }

        $code = trim($data['code'] ?? '');
        $name = trim($data['name'] ?? '');

        if (empty($code) || empty($name)) {
            throw new Exception('Ürün Kodu ve Malzeme Adı alanları zorunludur!');
        }

        if ($this->checkDuplicateCode($code, $id)) {
            throw new Exception("['{$code}'] ürün kodu sistemde başka bir üründe kullanılıyor!");
        }

        $barcode = trim($data['barcode'] ?? '');
        if (!empty($barcode) && $this->checkDuplicateBarcode($barcode, $id)) {
            throw new Exception("['{$barcode}'] barkod numarası başka bir üründe tanımlıdır!");
        }

        $existing = $_SESSION['mock_stocks'][$foundIdx];

        $_SESSION['mock_stocks'][$foundIdx]['code']          = strtoupper($code);
        $_SESSION['mock_stocks'][$foundIdx]['barcode']       = $barcode ?: null;
        $_SESSION['mock_stocks'][$foundIdx]['name']         = $name;
        $_SESSION['mock_stocks'][$foundIdx]['category']     = trim($data['category'] ?? $existing['category']);
        $_SESSION['mock_stocks'][$foundIdx]['category_id']  = (int)($data['category_id'] ?? $existing['category_id'] ?? 1);
        $_SESSION['mock_stocks'][$foundIdx]['unit']         = trim($data['unit'] ?? $existing['unit']);
        $_SESSION['mock_stocks'][$foundIdx]['min_threshold']= max(0, (int)($data['min_threshold'] ?? $existing['min_threshold']));
        $_SESSION['mock_stocks'][$foundIdx]['unit_price']   = max(0.0, (float)($data['unit_price'] ?? $existing['unit_price']));
        $_SESSION['mock_stocks'][$foundIdx]['brand']        = trim($data['brand'] ?? $existing['brand']);
        $_SESSION['mock_stocks'][$foundIdx]['specifications']= trim($data['specifications'] ?? $existing['specifications']);
        $_SESSION['mock_stocks'][$foundIdx]['description']  = trim($data['description'] ?? $existing['description']);
        $_SESSION['mock_stocks'][$foundIdx]['status']       = trim($data['status'] ?? $existing['status']);
        $_SESSION['mock_stocks'][$foundIdx]['updated_at']   = date('Y-m-d H:i:s');

        if (!empty($data['image_path'])) {
            $_SESSION['mock_stocks'][$foundIdx]['image_path'] = $data['image_path'];
        }

        if (function_exists('log_audit')) {
            log_audit('Ürün Güncellendi', 'Ürün Kataloğu', "[{$code}] {$name} temizlik ürünü güncellendi.");
        }

        return true;
    }

    /**
     * Transaction-Safe Stock Inflow (Stok Girişi)
     */
    public function addStockInflow(int $stockId, int $quantity, float $unitCost, string $notes = '', ?string $performedBy = null): bool
    {
        if ($quantity <= 0) {
            throw new Exception('Stok giriş miktarı 0\'dan büyük pozitif bir sayı olmalıdır!');
        }

        if ($unitCost < 0) {
            throw new Exception('Birim maliyet negatif olamaz!');
        }

        $product = $this->find($stockId);
        if (!$product) {
            throw new Exception('Stok girişi yapılacak ürün bulunamadı!');
        }

        $currentUser = Auth::user();
        $username = $performedBy ?? (($currentUser['first_name'] ?? 'Sistem') . ' ' . ($currentUser['last_name'] ?? 'Yöneticisi'));

        $prevStock = $product['quantity'];
        $nextStock = $prevStock + $quantity;
        $totalCost = $quantity * $unitCost;

        // Transaction simulation / Atomic array mutation
        $foundIdx = -1;
        foreach ($_SESSION['mock_stocks'] as $idx => $s) {
            if ((int)$s['id'] === $stockId) {
                $foundIdx = $idx;
                break;
            }
        }

        if ($foundIdx === -1) {
            throw new Exception('Stok işlemi sırasında ürün kaydı bulunamadı (Rollback)!');
        }

        // Update product stock and unit price
        $_SESSION['mock_stocks'][$foundIdx]['quantity']   = $nextStock;
        $_SESSION['mock_stocks'][$foundIdx]['unit_price'] = $unitCost > 0 ? $unitCost : $_SESSION['mock_stocks'][$foundIdx]['unit_price'];

        // Create stock movement log
        $docNo = 'EVR-' . date('Y') . '-' . str_pad(count($_SESSION['mock_stock_movements']) + 1, 4, '0', STR_PAD_LEFT);
        
        $movementLog = [
            'id'            => count($_SESSION['mock_stock_movements']) + 1,
            'document_no'   => $docNo,
            'stock_id'      => $stockId,
            'product_code'  => $product['code'],
            'item_name'     => $product['name'],
            'category'      => $product['category'],
            'movement_type' => 'STOK_GIRIS',
            'movement_label'=> 'Stok Girişi',
            'quantity'      => $quantity,
            'prev_stock'    => $prevStock,
            'next_stock'    => $nextStock,
            'unit_cost'     => $unitCost,
            'total_cost'    => $totalCost,
            'warehouse'     => $product['warehouse'] ?? 'Destek Hizmetleri Ana Deposu',
            'performed_by'  => $username,
            'date'          => date('Y-m-d H:i:s'),
            'description'   => $notes ?: 'Depo ambar stok girişi.'
        ];

        array_unshift($_SESSION['mock_stock_movements'], $movementLog);

        if (function_exists('log_audit')) {
            log_audit('Stok Girişi', 'Stok Yönetimi', "[{$product['code']}] {$product['name']} +{$quantity} {$product['unit']} giriş yapıldı. Yeni Stok: {$nextStock}");
        }

        return true;
    }

    /**
     * Transaction-Safe Stock Outflow (Stok Çıkışı) with Negative Stock Prevention
     */
    public function addStockOutflow(int $stockId, int $quantity, string $notes = '', ?string $performedBy = null): bool
    {
        // Auth / Permission Safety Check
        Auth::requirePermission('admin');

        if ($quantity <= 0) {
            throw new Exception('Stok çıkış miktarı 0\'dan büyük pozitif bir sayı olmalıdır!');
        }

        $product = $this->find($stockId);
        if (!$product) {
            throw new Exception('Stok çıkışı yapılacak ürün bulunamadı!');
        }

        $prevStock = $product['quantity'];

        // NEGATIVE STOCK PREVENTION CHECK (CRITICAL BUSINESS RULE)
        if ($prevStock < $quantity) {
            throw new Exception("Yetersiz stok. Mevcut stok: {$prevStock}");
        }

        $currentUser = Auth::user();
        $username = $performedBy ?? (($currentUser['first_name'] ?? 'Sistem') . ' ' . ($currentUser['last_name'] ?? 'Yöneticisi'));

        $nextStock = $prevStock - $quantity;
        $unitCost  = $product['unit_price'];
        $totalCost = $quantity * $unitCost;

        $foundIdx = -1;
        foreach ($_SESSION['mock_stocks'] as $idx => $s) {
            if ((int)$s['id'] === $stockId) {
                $foundIdx = $idx;
                break;
            }
        }

        if ($foundIdx === -1) {
            throw new Exception('Stok işlemi sırasında ürün kaydı bulunamadı (Rollback)!');
        }

        // Deduct quantity
        $_SESSION['mock_stocks'][$foundIdx]['quantity'] = $nextStock;

        // Create stock movement log
        $docNo = 'EVR-' . date('Y') . '-' . str_pad(count($_SESSION['mock_stock_movements']) + 1, 4, '0', STR_PAD_LEFT);
        
        $movementLog = [
            'id'            => count($_SESSION['mock_stock_movements']) + 1,
            'document_no'   => $docNo,
            'stock_id'      => $stockId,
            'product_code'  => $product['code'],
            'item_name'     => $product['name'],
            'category'      => $product['category'],
            'movement_type' => 'STOK_CIKIS',
            'movement_label'=> 'Stok Çıkışı',
            'quantity'      => $quantity,
            'prev_stock'    => $prevStock,
            'next_stock'    => $nextStock,
            'unit_cost'     => $unitCost,
            'total_cost'    => $totalCost,
            'warehouse'     => $product['warehouse'] ?? 'Destek Hizmetleri Ana Deposu',
            'performed_by'  => $username,
            'date'          => date('Y-m-d H:i:s'),
            'description'   => $notes ?: 'Destek Hizmetleri Yetkili stok çıkışı.'
        ];

        array_unshift($_SESSION['mock_stock_movements'], $movementLog);

        if (function_exists('log_audit')) {
            log_audit('Stok Çıkışı', 'Stok Yönetimi', "[{$product['code']}] {$product['name']} -{$quantity} {$product['unit']} çıkış yapıldı. Kalan Stok: {$nextStock}");
        }

        return true;
    }

    /**
     * Get All Stock Movement Logs (with product_id filter option)
     */
    public function getMovements(?int $stockId = null): array
    {
        $movements = $_SESSION['mock_stock_movements'] ?? [];

        if ($stockId !== null) {
            $movements = array_values(array_filter($movements, fn($m) => (int)($m['stock_id'] ?? 0) === $stockId));
        }

        usort($movements, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
        return $movements;
    }

    /**
     * Soft-Delete Cleaning Product (Soft Delete without breaking historical movements)
     */
    public function softDelete(int $id): bool
    {
        $foundIdx = -1;
        $product = null;

        foreach ($_SESSION['mock_stocks'] as $idx => $s) {
            if ((int)$s['id'] === $id) {
                $foundIdx = $idx;
                $product = $s;
                break;
            }
        }

        if ($foundIdx === -1 || !$product) {
            throw new Exception('Silinecek temizlik malzemesi bulunamadı!');
        }

        // Soft delete: update status and set deleted_at
        $_SESSION['mock_stocks'][$foundIdx]['status']     = 'Pasif';
        $_SESSION['mock_stocks'][$foundIdx]['deleted_at'] = date('Y-m-d H:i:s');

        // Move to TrashBin model for soft delete tracking
        $trashModel = new Trash();
        $trashModel->softDelete('stock', $product['id'], $product['code'], $product['name'], $product);

        if (function_exists('log_audit')) {
            log_audit('Ürün Pasife Alındı', 'Ürün Kataloğu', "[{$product['code']}] {$product['name']} ürünü çöp kutusuna taşındı.");
        }

        return true;
    }
}
