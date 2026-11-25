<?php

declare(strict_types=1);

namespace App\Services;

/**
 * CampusStock Kırtasiye Mock Veri Servisi
 *
 * CampusStockDb.drawio şemasıyla %100 uyumlu varlıkları (Üniversite, Kırtasiye,
 * Kategori, Ürün, Stok Hareket, Sepet, Sipariş, Sipariş Detay, Ödeme, Kargo, İade, Kullanıcı)
 * session tabanlı kalıcı MOCK veri olarak yönetir.
 */
final class MockDataService
{
    private const SESSION_KEY = 'campus_mock_db_v1';

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = self::getDefaultData();
        }
    }

    public static function reset(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION[self::SESSION_KEY] = self::getDefaultData();
    }

    public static function all(string $table): array
    {
        self::init();
        return $_SESSION[self::SESSION_KEY][$table] ?? [];
    }

    public static function saveAll(string $table, array $rows): void
    {
        self::init();
        $_SESSION[self::SESSION_KEY][$table] = array_values($rows);
    }

    public static function find(string $table, int $id): ?array
    {
        foreach (self::all($table) as $row) {
            if ((int)($row['id'] ?? 0) === $id) {
                return $row;
            }
        }
        return null;
    }

    public static function nextId(string $table): int
    {
        $max = 0;
        foreach (self::all($table) as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id > $max) {
                $max = $id;
            }
        }
        return $max + 1;
    }

    private static function getDefaultData(): array
    {
        $today = date('Y-m-d');
        $now   = date('Y-m-d H:i:s');

        return [
            'universities' => [
                [
                    'id'        => 1,
                    'name'      => 'İstanbul Teknik Üniversitesi (İTÜ)',
                    'city'      => 'İstanbul',
                    'address'   => 'Ayazağa Kampüsü, Maslak, Sarıyer',
                    'phone'     => '0212 285 30 00',
                    'code'      => 'ITU',
                    'is_active' => 1
                ],
                [
                    'id'        => 2,
                    'name'      => 'Orta Doğu Teknik Üniversitesi (ODTÜ)',
                    'city'      => 'Ankara',
                    'address'   => 'Üniversiteler Mah. Dumlupınar Blv. No:1, Çankaya',
                    'phone'     => '0312 210 20 00',
                    'code'      => 'ODTU',
                    'is_active' => 1
                ],
                [
                    'id'        => 3,
                    'name'      => 'Boğaziçi Üniversitesi',
                    'city'      => 'İstanbul',
                    'address'   => 'Bebek, Beşiktaş',
                    'phone'     => '0212 359 54 00',
                    'code'      => 'BOUN',
                    'is_active' => 1
                ]
            ],
            'stores' => [
                [
                    'id'              => 1,
                    'university_id'   => 1,
                    'university_name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                    'name'            => 'İTÜ Ayazağa Merkez Kırtasiye',
                    'address'         => '75. Yıl Öğrenci Merkezi Zemin Kat, Sarıyer/İstanbul',
                    'phone'           => '0212 285 30 10',
                    'tax_number'      => '1234567890',
                    'opens_at'        => '08:30',
                    'closes_at'       => '19:30',
                    'is_active'       => 1
                ],
                [
                    'id'              => 2,
                    'university_id'   => 1,
                    'university_name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                    'name'            => 'İTÜ Maçka Kampüs Kırtasiyesi',
                    'address'         => 'Yabancı Diller Yüksekokulu Karşısı, Şişli/İstanbul',
                    'phone'           => '0212 293 13 05',
                    'tax_number'      => '1234567891',
                    'opens_at'        => '09:00',
                    'closes_at'       => '18:00',
                    'is_active'       => 1
                ],
                [
                    'id'              => 3,
                    'university_id'   => 2,
                    'university_name' => 'Orta Doğu Teknik Üniversitesi (ODTÜ)',
                    'name'            => 'ODTÜ Çarşı Kırtasiye',
                    'address'         => 'ODTÜ Çarşı Binası No:14, Çankaya/Ankara',
                    'phone'           => '0312 210 40 50',
                    'tax_number'      => '9876543210',
                    'opens_at'        => '08:30',
                    'closes_at'       => '18:30',
                    'is_active'       => 1
                ]
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Kalem & Yazı Gereçleri', 'slug' => 'kalem-yazi', 'description' => 'Versatil, tükenmez, dolma ve fosforlu kalemler', 'product_count' => 4],
                ['id' => 2, 'name' => 'Defter & Kağıt Ürünleri', 'slug' => 'defter-kagit', 'description' => 'Spiralli defterler, bloknotlar, A4 fotokopi kağıtları', 'product_count' => 3],
                ['id' => 3, 'name' => 'Boya & Teknik Çizim', 'slug' => 'boya-cizim', 'description' => 'Kuru boya, guaj, T-cetveli ve teknik çizim setleri', 'product_count' => 2],
                ['id' => 4, 'name' => 'Dosyalama & Arşivleme', 'slug' => 'dosyalama', 'description' => 'Klasörler, sunum dosyaları ve ayraçlar', 'product_count' => 2],
                ['id' => 5, 'name' => 'Ofis & Okul Gereçleri', 'slug' => 'ofis-okul', 'description' => 'Hesap makineleri, yapıştırıcı, silgi ve zımba', 'product_count' => 3]
            ],
            'brands' => [
                ['id' => 1, 'name' => 'Faber-Castell', 'country' => 'Almanya', 'is_active' => 1],
                ['id' => 2, 'name' => 'Rotring', 'country' => 'Almanya', 'is_active' => 1],
                ['id' => 3, 'name' => 'Gıpta', 'country' => 'Türkiye', 'is_active' => 1],
                ['id' => 4, 'name' => 'Stabilo', 'country' => 'Almanya', 'is_active' => 1],
                ['id' => 5, 'name' => 'Bic', 'country' => 'Fransa', 'is_active' => 1],
                ['id' => 6, 'name' => 'Leitz', 'country' => 'Almanya', 'is_active' => 1],
                ['id' => 7, 'name' => 'Mopak', 'country' => 'Türkiye', 'is_active' => 1],
                ['id' => 8, 'name' => 'Casio', 'country' => 'Japonya', 'is_active' => 1]
            ],
            'products' => [
                [
                    'id' => 1, 'category_id' => 1, 'brand_id' => 1, 'store_id' => 1,
                    'category_name' => 'Kalem & Yazı Gereçleri', 'brand_name' => 'Faber-Castell',
                    'code' => 'KRT-FC-001', 'barcode' => '8690846001015',
                    'name' => 'Faber-Castell Grip 2011 0.7mm Versatil Kalem - Gece Mavisi',
                    'description' => 'Ergonomik üçgen gövde, kaymaz grip noktaları ve gizlenebilir uç.',
                    'unit' => 'Adet', 'purchase_price' => 110.00, 'sale_price' => 185.00,
                    'wholesale_price' => 150.00, 'tax_rate' => 20.0,
                    'current_stock' => 45, 'min_stock' => 10, 'shelf_location' => 'Reyon A-1 / Raf 2',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 2, 'category_id' => 1, 'brand_id' => 2, 'store_id' => 1,
                    'category_name' => 'Kalem & Yazı Gereçleri', 'brand_name' => 'Rotring',
                    'code' => 'KRT-RT-002', 'barcode' => '8690846001022',
                    'name' => 'Rotring Tikky 0.7mm Mekanik Kurşun Kalem - Siyah',
                    'description' => 'Teknik çizim ve günlük notlar için hassas pirinç mekanizma.',
                    'unit' => 'Adet', 'purchase_price' => 95.00, 'sale_price' => 160.00,
                    'wholesale_price' => 130.00, 'tax_rate' => 20.0,
                    'current_stock' => 6, 'min_stock' => 12, 'shelf_location' => 'Reyon A-1 / Raf 3',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 3, 'category_id' => 2, 'brand_id' => 3, 'store_id' => 1,
                    'category_name' => 'Defter & Kağıt Ürünleri', 'brand_name' => 'Gıpta',
                    'code' => 'KRT-GP-003', 'barcode' => '8690846001039',
                    'name' => 'Gıpta A4 120 Yaprak PP Kapak Spiralli Kareli Defter',
                    'description' => '80 gr 1. hamur kağıt, dayanıklı polipropilen kapak.',
                    'unit' => 'Adet', 'purchase_price' => 65.00, 'sale_price' => 115.00,
                    'wholesale_price' => 90.00, 'tax_rate' => 10.0,
                    'current_stock' => 84, 'min_stock' => 20, 'shelf_location' => 'Reyon B-2 / Raf 1',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 4, 'category_id' => 2, 'brand_id' => 7, 'store_id' => 1,
                    'category_name' => 'Defter & Kağıt Ürünleri', 'brand_name' => 'Mopak',
                    'code' => 'KRT-MP-004', 'barcode' => '8690846001046',
                    'name' => 'Mopak A4 80gr Fotokopi Kağıdı (500 Yaprak)',
                    'description' => 'Lazer ve mürekkep püskürtmeli yazıcılar için yüksek beyazlık.',
                    'unit' => 'Paket', 'purchase_price' => 125.00, 'sale_price' => 175.00,
                    'wholesale_price' => 150.00, 'tax_rate' => 20.0,
                    'current_stock' => 120, 'min_stock' => 25, 'shelf_location' => 'Depo Zemin / Palet 1',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 5, 'category_id' => 1, 'brand_id' => 4, 'store_id' => 1,
                    'category_name' => 'Kalem & Yazı Gereçleri', 'brand_name' => 'Stabilo',
                    'code' => 'KRT-ST-005', 'barcode' => '8690846001053',
                    'name' => 'Stabilo Boss Original Pastel 6\'lı Fosforlu Kalem Seti',
                    'description' => 'Kurumaya karşı 4 saat kapaksız koruma teknolojisi.',
                    'unit' => 'Set', 'purchase_price' => 165.00, 'sale_price' => 270.00,
                    'wholesale_price' => 220.00, 'tax_rate' => 20.0,
                    'current_stock' => 4, 'min_stock' => 8, 'shelf_location' => 'Reyon A-2 / Raf 1',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 6, 'category_id' => 1, 'brand_id' => 5, 'store_id' => 1,
                    'category_name' => 'Kalem & Yazı Gereçleri', 'brand_name' => 'Bic',
                    'code' => 'KRT-BC-006', 'barcode' => '8690846001060',
                    'name' => 'Bic Cristal Medium 1.0mm Tükenmez Kalem - Mavi',
                    'description' => '2 km yazım uzunluğu, şeffaf altıgen gövde.',
                    'unit' => 'Adet', 'purchase_price' => 11.00, 'sale_price' => 22.50,
                    'wholesale_price' => 16.00, 'tax_rate' => 20.0,
                    'current_stock' => 230, 'min_stock' => 50, 'shelf_location' => 'Kasa Önü Stand 1',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 7, 'category_id' => 3, 'brand_id' => 1, 'store_id' => 1,
                    'category_name' => 'Boya & Teknik Çizim', 'brand_name' => 'Faber-Castell',
                    'code' => 'KRT-FC-007', 'barcode' => '8690846001077',
                    'name' => 'Faber-Castell 24\'lü Tam Boy Metal Kutu Kuru Boya',
                    'description' => 'SV yapıştırma sistemi ile kırılmaya dirençli uç.',
                    'unit' => 'Kutu', 'purchase_price' => 210.00, 'sale_price' => 345.00,
                    'wholesale_price' => 285.00, 'tax_rate' => 10.0,
                    'current_stock' => 18, 'min_stock' => 5, 'shelf_location' => 'Reyon C-1 / Raf 2',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 8, 'category_id' => 4, 'brand_id' => 6, 'store_id' => 1,
                    'category_name' => 'Dosyalama & Arşivleme', 'brand_name' => 'Leitz',
                    'code' => 'KRT-LZ-008', 'barcode' => '8690846001084',
                    'name' => 'Leitz 1010 Geniş Plastik Klasör A4 - Lacivert',
                    'description' => '180 derece açılabilen patentli mekanizma, 600 sayfa kapasite.',
                    'unit' => 'Adet', 'purchase_price' => 88.00, 'sale_price' => 145.00,
                    'wholesale_price' => 120.00, 'tax_rate' => 20.0,
                    'current_stock' => 32, 'min_stock' => 10, 'shelf_location' => 'Reyon D-1 / Raf 1',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 9, 'category_id' => 5, 'brand_id' => 8, 'store_id' => 1,
                    'category_name' => 'Ofis & Okul Gereçleri', 'brand_name' => 'Casio',
                    'code' => 'KRT-CS-009', 'barcode' => '8690846001091',
                    'name' => 'Casio FX-82MS 240 Fonksiyonlu Bilimsel Hesap Makinesi',
                    'description' => 'Mühendislik sınavları onaylı 2 satır LCD ekranlı bilimsel hesap makinesi.',
                    'unit' => 'Adet', 'purchase_price' => 480.00, 'sale_price' => 750.00,
                    'wholesale_price' => 640.00, 'tax_rate' => 20.0,
                    'current_stock' => 3, 'min_stock' => 5, 'shelf_location' => 'Kasa Arkası Vitrin',
                    'image_url' => '', 'status' => 'active'
                ],
                [
                    'id' => 10, 'category_id' => 5, 'brand_id' => 1, 'store_id' => 1,
                    'category_name' => 'Ofis & Okul Gereçleri', 'brand_name' => 'Faber-Castell',
                    'code' => 'KRT-FC-010', 'barcode' => '8690846001107',
                    'name' => 'Faber-Castell Dust-Free Sınav Silgisi (Beyaz)',
                    'description' => 'İz bırakmayan, PVC içermeyen tozsuz silgi.',
                    'unit' => 'Adet', 'purchase_price' => 14.00, 'sale_price' => 28.00,
                    'wholesale_price' => 20.00, 'tax_rate' => 20.0,
                    'current_stock' => 140, 'min_stock' => 30, 'shelf_location' => 'Kasa Önü Stand 2',
                    'image_url' => '', 'status' => 'active'
                ]
            ],
            'stock_movements' => [
                ['id' => 1, 'product_id' => 1, 'product_name' => 'Faber-Castell Grip 2011 0.7mm', 'movement_type' => 'purchase', 'quantity' => 50, 'prev_stock' => 0, 'next_stock' => 50, 'unit_cost' => 110.00, 'notes' => 'Açılış toptan tedarik girişi', 'created_at' => date('Y-m-d 09:15:00', strtotime('-2 days'))],
                ['id' => 2, 'product_id' => 1, 'product_name' => 'Faber-Castell Grip 2011 0.7mm', 'movement_type' => 'sale', 'quantity' => -5, 'prev_stock' => 50, 'next_stock' => 45, 'unit_cost' => 110.00, 'notes' => 'POS & Öğrenci Siparişi çıkışı', 'created_at' => date('Y-m-d 14:20:00', strtotime('-1 day'))],
                ['id' => 3, 'product_id' => 9, 'product_name' => 'Casio FX-82MS Hesap Makinesi', 'movement_type' => 'sale', 'quantity' => -2, 'prev_stock' => 5, 'next_stock' => 3, 'unit_cost' => 480.00, 'notes' => 'Vize haftası yoğun satış', 'created_at' => "{$today} 10:05:00"],
                ['id' => 4, 'product_id' => 3, 'product_name' => 'Gıpta A4 120 Yaprak Spiralli Defter', 'movement_type' => 'purchase', 'quantity' => 40, 'prev_stock' => 44, 'next_stock' => 84, 'unit_cost' => 65.00, 'notes' => 'Dönem başı stok takviyesi', 'created_at' => "{$today} 08:45:00"]
            ],
            'sales' => [
                [
                    'id' => 1,
                    'receipt_no' => 'KRT-' . date('Ymd') . '-0001',
                    'customer_id' => 1,
                    'customer_name' => 'Ali Kaya (İTÜ Öğrenci)',
                    'store_id' => 1,
                    'store_name' => 'İTÜ Ayazağa Merkez Kırtasiye',
                    'sale_type' => 'online',
                    'payment_method' => 'credit_card',
                    'payment_status' => 'Ödendi',
                    'order_status' => 'Teslim Edildi',
                    'cargo_company' => 'Kampüs Gel-Al',
                    'cargo_tracking' => 'CS-4821',
                    'cargo_fee' => 0.00,
                    'rating' => 5,
                    'review' => 'Kalemler çok hızlı hazırlandı, ders arasında hemen teslim aldım.',
                    'subtotal' => 300.00,
                    'tax_total' => 50.00,
                    'discount_total' => 0.00,
                    'grand_total' => 300.00,
                    'status' => 'completed',
                    'created_at' => "{$today} 09:30:00",
                    'items' => [
                        ['id' => 1, 'product_id' => 1, 'product_name' => 'Faber-Castell Grip 2011 0.7mm Versatil Kalem', 'quantity' => 1, 'unit_price' => 185.00, 'line_total' => 185.00, 'returned_quantity' => 0],
                        ['id' => 2, 'product_id' => 3, 'product_name' => 'Gıpta A4 120 Yaprak PP Kapak Spiralli Kareli Defter', 'quantity' => 1, 'unit_price' => 115.00, 'line_total' => 115.00, 'returned_quantity' => 0]
                    ]
                ],
                [
                    'id' => 2,
                    'receipt_no' => 'KRT-' . date('Ymd') . '-0002',
                    'customer_id' => 2,
                    'customer_name' => 'Zeynep Yılmaz (İTÜ Mimarlık)',
                    'store_id' => 1,
                    'store_name' => 'İTÜ Ayazağa Merkez Kırtasiye',
                    'sale_type' => 'online',
                    'payment_method' => 'credit_card',
                    'payment_status' => 'Ödendi',
                    'order_status' => 'Hazırlanıyor',
                    'cargo_company' => 'Yurtiçi Kargo',
                    'cargo_tracking' => 'YK-9938120',
                    'cargo_fee' => 45.00,
                    'rating' => null,
                    'review' => null,
                    'subtotal' => 750.00,
                    'tax_total' => 125.00,
                    'discount_total' => 0.00,
                    'grand_total' => 795.00,
                    'status' => 'completed',
                    'created_at' => "{$today} 11:15:00",
                    'items' => [
                        ['id' => 3, 'product_id' => 9, 'product_name' => 'Casio FX-82MS 240 Fonksiyonlu Bilimsel Hesap Makinesi', 'quantity' => 1, 'unit_price' => 750.00, 'line_total' => 750.00, 'returned_quantity' => 0]
                    ]
                ],
                [
                    'id' => 3,
                    'receipt_no' => 'KRT-' . date('Ymd') . '-0003',
                    'customer_id' => null,
                    'customer_name' => 'Peşin Kasa Müşterisi',
                    'store_id' => 1,
                    'store_name' => 'İTÜ Ayazağa Merkez Kırtasiye',
                    'sale_type' => 'pos',
                    'payment_method' => 'cash',
                    'payment_status' => 'Ödendi',
                    'order_status' => 'Teslim Edildi',
                    'cargo_company' => 'Mağaza Kasa',
                    'cargo_tracking' => '-',
                    'cargo_fee' => 0.00,
                    'rating' => null,
                    'review' => null,
                    'subtotal' => 197.50,
                    'tax_total' => 32.92,
                    'discount_total' => 0.00,
                    'grand_total' => 197.50,
                    'status' => 'completed',
                    'created_at' => "{$today} 13:40:00",
                    'items' => [
                        ['id' => 4, 'product_id' => 4, 'product_name' => 'Mopak A4 80gr Fotokopi Kağıdı (500 Yaprak)', 'quantity' => 1, 'unit_price' => 175.00, 'line_total' => 175.00, 'returned_quantity' => 0],
                        ['id' => 5, 'product_id' => 6, 'product_name' => 'Bic Cristal Medium 1.0mm Tükenmez Kalem - Mavi', 'quantity' => 1, 'unit_price' => 22.50, 'line_total' => 22.50, 'returned_quantity' => 0]
                    ]
                ]
            ],
            'returns' => [
                [
                    'id'           => 1,
                    'sale_id'      => 1,
                    'receipt_no'   => 'KRT-' . date('Ymd', strtotime('-1 day')) . '-0008',
                    'product_name' => 'Rotring Tikky 0.7mm Mekanik Kurşun Kalem',
                    'customer_name'=> 'Mehmet Demir',
                    'reason'       => 'Yanlış uç kalınlığı (0.5mm yerine 0.7mm alınmış)',
                    'return_amount'=> 160.00,
                    'status'       => 'Onaylandı',
                    'created_at'   => date('Y-m-d 16:00:00', strtotime('-1 day'))
                ]
            ],
            'customers' => [
                [
                    'id' => 1, 'customer_type' => 'student', 'first_name' => 'Ali', 'last_name' => 'Kaya',
                    'email' => 'ali.kaya@itu.edu.tr', 'phone' => '0532 111 22 33',
                    'city' => 'İstanbul', 'district' => 'Sarıyer', 'neighborhood' => 'Maslak Mah.', 'street' => 'Ayazağa Cad. No:4', 'zip_code' => '34469',
                    'discount_rate' => 5.0, 'credit_limit' => 1000.00, 'balance' => 0.00, 'status' => 'active'
                ],
                [
                    'id' => 2, 'customer_type' => 'student', 'first_name' => 'Zeynep', 'last_name' => 'Yılmaz',
                    'email' => 'zeynep.yilmaz@itu.edu.tr', 'phone' => '0544 222 33 44',
                    'city' => 'İstanbul', 'district' => 'Beşiktaş', 'neighborhood' => 'Sinanpaşa Mah.', 'street' => 'Barbaros Blv. No:12', 'zip_code' => '34353',
                    'discount_rate' => 5.0, 'credit_limit' => 1500.00, 'balance' => 240.00, 'status' => 'active'
                ],
                [
                    'id' => 3, 'customer_type' => 'teacher', 'first_name' => 'Dr. Ahmet', 'last_name' => 'Çelik',
                    'email' => 'acelik@itu.edu.tr', 'phone' => '0505 333 44 55',
                    'city' => 'İstanbul', 'district' => 'Sarıyer', 'neighborhood' => 'Reşitpaşa Mah.', 'street' => 'Tuncay Artun Cad. No:22', 'zip_code' => '34467',
                    'discount_rate' => 10.0, 'credit_limit' => 5000.00, 'balance' => 0.00, 'status' => 'active'
                ]
            ],
            'users' => [
                [
                    'id' => 1, 'username' => 'admin', 'email' => 'admin@campusstock.local',
                    'first_name' => 'Fatih', 'last_name' => 'Yönetici', 'phone' => '0555 111 22 33',
                    'role_id' => 1, 'role_slug' => 'admin', 'role_name' => 'Genel Admin',
                    'title' => 'Platform Yöneticisi', 'status' => 'active',
                    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT)
                ],
                [
                    'id' => 2, 'username' => 'kasiyer', 'email' => 'kadmin@campusstock.local',
                    'first_name' => 'Ayşe', 'last_name' => 'Demir', 'phone' => '0555 222 33 44',
                    'role_id' => 2, 'role_slug' => 'kadmin', 'role_name' => 'Kırtasiye Yöneticisi (KAdmin)',
                    'title' => 'İTÜ Ayazağa Mağaza Sorumlusu', 'status' => 'active',
                    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT)
                ],
                [
                    'id' => 3, 'username' => 'uye_ali', 'email' => 'ali.kaya@itu.edu.tr',
                    'first_name' => 'Ali', 'last_name' => 'Kaya', 'phone' => '0532 111 22 33',
                    'role_id' => 3, 'role_slug' => 'uye', 'role_name' => 'Üye (Öğrenci)',
                    'title' => 'Öğrenci Üye', 'status' => 'active',
                    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT)
                ]
            ]
        ];
    }
}
