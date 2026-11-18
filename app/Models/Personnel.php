<?php

namespace App\Models;

class Personnel
{
    public function __construct()
    {
        $this->initMockSessionData();
    }

    /**
     * Initialize mock personnel dataset in session if DB table is not ready
     */
    private function initMockSessionData(): void
    {
        if (!isset($_SESSION['mock_personnel']) || count($_SESSION['mock_personnel']) < 15) {
            $_SESSION['mock_personnel'] = [
                ['id'=>1, 'sicil_no'=>'SIC-1001', 'tc_no'=>'38491029384', 'first_name'=>'Ahmet', 'last_name'=>'Yılmaz', 'title'=>'Bilgi İşlem Müdürü', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Sistem & Ağ Birimi', 'phone'=>'0532 100 0001 (D: 1001)', 'email'=>'ahmet.yilmaz@belediye.bel.tr', 'room_no'=>'Kat 2 Oda 204', 'status'=>'Aktif', 'hire_date'=>'2018-05-10'],
                ['id'=>2, 'sicil_no'=>'SIC-1002', 'tc_no'=>'49281039485', 'first_name'=>'Fatma', 'last_name'=>'Şahin', 'title'=>'Şef', 'department'=>'Yazı İşleri Müdürlüğü', 'unit'=>'Evrak Kayıt Birimi', 'phone'=>'0532 100 0002 (D: 1102)', 'email'=>'fatma.sahin@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 112', 'status'=>'Aktif', 'hire_date'=>'2019-02-15'],
                ['id'=>3, 'sicil_no'=>'SIC-1003', 'tc_no'=>'58291049586', 'first_name'=>'Mehmet', 'last_name'=>'Demir', 'title'=>'Mimar', 'department'=>'İmar ve Şehircilik Müdürlüğü', 'unit'=>'Proje Onay Birimi', 'phone'=>'0532 100 0003 (D: 1305)', 'email'=>'mehmet.demir@belediye.bel.tr', 'room_no'=>'Kat 3 Oda 305', 'status'=>'Aktif', 'hire_date'=>'2020-08-20'],
                ['id'=>4, 'sicil_no'=>'SIC-1004', 'tc_no'=>'29384756102', 'first_name'=>'Ali', 'last_name'=>'Kaya', 'title'=>'İnşaat Mühendisi', 'department'=>'Fen İşleri Müdürlüğü', 'unit'=>'Şantiye Takip Birimi', 'phone'=>'0532 100 0004 (D: 1400)', 'email'=>'ali.kaya@belediye.bel.tr', 'room_no'=>'Ek Hizmet Binası Oda 12', 'status'=>'Aktif', 'hire_date'=>'2021-03-01'],
                ['id'=>5, 'sicil_no'=>'SIC-1005', 'tc_no'=>'10293847561', 'first_name'=>'Ayşe', 'last_name'=>'Kaya', 'title'=>'Depo Sorumlusu', 'department'=>'Destek Hizmetleri', 'unit'=>'Merkez Ambar Birimi', 'phone'=>'0532 100 0005 (D: 1500)', 'email'=>'ayse.kaya@belediye.bel.tr', 'room_no'=>'Zemin Kat Ambar', 'status'=>'Aktif', 'hire_date'=>'2022-01-10'],
                ['id'=>6, 'sicil_no'=>'SIC-1006', 'tc_no'=>'19203948576', 'first_name'=>'Mustafa', 'last_name'=>'Öztürk', 'title'=>'Zabıta Komiseri', 'department'=>'Zabıta Müdürlüğü', 'unit'=>'Devriye Ekibi', 'phone'=>'0532 100 0006 (D: 1700)', 'email'=>'mustafa.ozturk@belediye.bel.tr', 'room_no'=>'Zabıta Karakolu', 'status'=>'Aktif', 'hire_date'=>'2017-03-14'],
                ['id'=>7, 'sicil_no'=>'SIC-1007', 'tc_no'=>'69302918475', 'first_name'=>'Zeynep', 'last_name'=>'Çelik', 'title'=>'Sosyolog', 'department'=>'Kültür ve Sosyal İşler', 'unit'=>'Sosyal Yardım Birimi', 'phone'=>'0532 100 0007 (D: 1802)', 'email'=>'zeynep.celik@belediye.bel.tr', 'room_no'=>'Kültür Merkezi Oda 12', 'status'=>'Pasif', 'hire_date'=>'2023-04-01'],
                ['id'=>8, 'sicil_no'=>'SIC-1008', 'tc_no'=>'78291039481', 'first_name'=>'Burak', 'last_name'=>'Arslan', 'title'=>'Yazılım Uzmanı', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Yazılım Geliştirme Birimi', 'phone'=>'0532 100 0008 (D: 1008)', 'email'=>'burak.arslan@belediye.bel.tr', 'room_no'=>'Kat 2 Oda 205', 'status'=>'Aktif', 'hire_date'=>'2022-06-15'],
                ['id'=>9, 'sicil_no'=>'SIC-1009', 'tc_no'=>'89301928374', 'first_name'=>'Canan', 'last_name'=>'Yıldız', 'title'=>'Mali Hizmetler Uzmanı', 'department'=>'Mali Hizmetler', 'unit'=>'Bütçe ve Muhasebe', 'phone'=>'0532 100 0009 (D: 1201)', 'email'=>'canan.yildiz@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 108', 'status'=>'Aktif', 'hire_date'=>'2019-11-01'],
                ['id'=>10, 'sicil_no'=>'SIC-1010', 'tc_no'=>'90481029384', 'first_name'=>'Deniz', 'last_name'=>'Aydın', 'title'=>'İnsan Kaynakları Uzmanı', 'department'=>'İnsan Kaynakları', 'unit'=>'Özlük İşleri', 'phone'=>'0532 100 0010 (D: 1105)', 'email'=>'deniz.aydin@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 110', 'status'=>'Aktif', 'hire_date'=>'2020-02-10'],
                ['id'=>11, 'sicil_no'=>'SIC-1011', 'tc_no'=>'12345678901', 'first_name'=>'Emre', 'last_name'=>'Korkmaz', 'title'=>'Ağ ve Güvenlik Uzmanı', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Sistem & Ağ Birimi', 'phone'=>'0532 100 0011 (D: 1004)', 'email'=>'emre.korkmaz@belediye.bel.tr', 'room_no'=>'Kat 2 Oda 204', 'status'=>'Aktif', 'hire_date'=>'2021-09-01'],
                ['id'=>12, 'sicil_no'=>'SIC-1012', 'tc_no'=>'23456789012', 'first_name'=>'Gülşen', 'last_name'=>'Yılmaz', 'title'=>'Veri Giriş Personeli', 'department'=>'Yazı İşleri Müdürlüğü', 'unit'=>'Genel Evrak', 'phone'=>'0532 100 0012 (D: 1103)', 'email'=>'gulsen.yilmaz@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 113', 'status'=>'Aktif', 'hire_date'=>'2022-03-15'],
                ['id'=>13, 'sicil_no'=>'SIC-1013', 'tc_no'=>'34567890123', 'first_name'=>'Hakan', 'last_name'=>'Şen', 'title'=>'Şehir Plancısı', 'department'=>'İmar ve Şehircilik Müdürlüğü', 'unit'=>'Harita Birimi', 'phone'=>'0532 100 0013 (D: 1308)', 'email'=>'hakan.sen@belediye.bel.tr', 'room_no'=>'Kat 3 Oda 308', 'status'=>'Aktif', 'hire_date'=>'2018-10-10'],
                ['id'=>14, 'sicil_no'=>'SIC-1014', 'tc_no'=>'45678901234', 'first_name'=>'İbrahim', 'last_name'=>'Güneş', 'title'=>'Saha Süpervizörü', 'department'=>'Fen İşleri Müdürlüğü', 'unit'=>'Yol Bakım Birimi', 'phone'=>'0532 100 0014 (D: 1410)', 'email'=>'ibrahim.gunes@belediye.bel.tr', 'room_no'=>'Ek Binası 3', 'status'=>'Aktif', 'hire_date'=>'2016-04-20'],
                ['id'=>15, 'sicil_no'=>'SIC-1015', 'tc_no'=>'56789012345', 'first_name'=>'Kubilay', 'last_name'=>'Tekin', 'title'=>'Satınalma Memuru', 'department'=>'Destek Hizmetleri', 'unit'=>'İhale ve Satınalma', 'phone'=>'0532 100 0015 (D: 1505)', 'email'=>'kubilay.tekin@belediye.bel.tr', 'room_no'=>'Zemin Kat Oda 5', 'status'=>'Aktif', 'hire_date'=>'2020-07-01'],
                ['id'=>16, 'sicil_no'=>'SIC-1016', 'tc_no'=>'67890123456', 'first_name'=>'Leyla', 'last_name'=>'Acar', 'title'=>'Muhasebe Memuru', 'department'=>'Mali Hizmetler', 'unit'=>'Gelirler Birimi', 'phone'=>'0532 100 0016 (D: 1205)', 'email'=>'leyla.acar@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 106', 'status'=>'Aktif', 'hire_date'=>'2021-01-15'],
                ['id'=>17, 'sicil_no'=>'SIC-1017', 'tc_no'=>'78901234567', 'first_name'=>'Murat', 'last_name'=>'Bulut', 'title'=>'Zabıta Memuru', 'department'=>'Zabıta Müdürlüğü', 'unit'=>'Ruhsat Denetim', 'phone'=>'0532 100 0017 (D: 1705)', 'email'=>'murat.bulut@belediye.bel.tr', 'room_no'=>'Zabıta Karakolu', 'status'=>'Aktif', 'hire_date'=>'2019-08-10'],
                ['id'=>18, 'sicil_no'=>'SIC-1018', 'tc_no'=>'89012345678', 'first_name'=>'Nesrin', 'last_name'=>'Erdoğan', 'title'=>'Kütüphane Sorumlusu', 'department'=>'Kültür ve Sosyal İşler', 'unit'=>'Kütüphane Birimi', 'phone'=>'0532 100 0018 (D: 1810)', 'email'=>'nesrin.erdogan@belediye.bel.tr', 'room_no'=>'Merkez Kütüphane', 'status'=>'Aktif', 'hire_date'=>'2017-12-01'],
                ['id'=>19, 'sicil_no'=>'SIC-1019', 'tc_no'=>'90123456789', 'first_name'=>'Ogün', 'last_name'=>'Soylu', 'title'=>'Donanım Teknikeri', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Saha Destek Birimi', 'phone'=>'0532 100 0019 (D: 1002)', 'email'=>'ogun.soylu@belediye.bel.tr', 'room_no'=>'Kat 2 Oda 202', 'status'=>'Aktif', 'hire_date'=>'2022-10-01'],
                ['id'=>20, 'sicil_no'=>'SIC-1020', 'tc_no'=>'11223344556', 'first_name'=>'Ömer', 'last_name'=>'Farak', 'title'=>'Eğitmen', 'department'=>'Kültür ve Sosyal İşler', 'unit'=>'Gençlik Merkezi', 'phone'=>'0532 100 0020 (D: 1820)', 'email'=>'omer.farak@belediye.bel.tr', 'room_no'=>'Gençlik Merkezi', 'status'=>'Aktif', 'hire_date'=>'2023-01-15'],
                ['id'=>21, 'sicil_no'=>'SIC-1021', 'tc_no'=>'22334455667', 'first_name'=>'Pınar', 'last_name'=>'Dönmez', 'title'=>'E-Belediye Sistem Uzmanı', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Yazılım Geliştirme Birimi', 'phone'=>'0532 100 0021 (D: 1009)', 'email'=>'pinar.donmez@belediye.bel.tr', 'room_no'=>'Kat 2 Oda 205', 'status'=>'Aktif', 'hire_date'=>'2020-11-15'],
                ['id'=>22, 'sicil_no'=>'SIC-1022', 'tc_no'=>'33445566778', 'first_name'=>'Rıza', 'last_name'=>'Kocaoğlu', 'title'=>'İnşaat Teknikeri', 'department'=>'Fen İşleri Müdürlüğü', 'unit'=>'Altyapı Birimi', 'phone'=>'0532 100 0022 (D: 1412)', 'email'=>'riza.kocaoglu@belediye.bel.tr', 'room_no'=>'Şantiye Binası', 'status'=>'Aktif', 'hire_date'=>'2018-09-01'],
                ['id'=>23, 'sicil_no'=>'SIC-1023', 'tc_no'=>'44556677889', 'first_name'=>'Selim', 'last_name'=>'Yavuz', 'title'=>'Harita Mühendisi', 'department'=>'İmar ve Şehircilik Müdürlüğü', 'unit'=>'CBS Birimi', 'phone'=>'0532 100 0023 (D: 1310)', 'email'=>'selim.yavuz@belediye.bel.tr', 'room_no'=>'Kat 3 Oda 310', 'status'=>'Aktif', 'hire_date'=>'2021-05-20'],
                ['id'=>24, 'sicil_no'=>'SIC-1024', 'tc_no'=>'55667788990', 'first_name'=>'Sinem', 'last_name'=>'Bozkurt', 'title'=>'Halkla İlişkiler Uzmanı', 'department'=>'Yazı İşleri Müdürlüğü', 'unit'=>'Basın ve Halkla İlişkiler', 'phone'=>'0532 100 0024 (D: 1120)', 'email'=>'sinem.bozkurt@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 115', 'status'=>'Aktif', 'hire_date'=>'2022-08-10'],
                ['id'=>25, 'sicil_no'=>'SIC-1025', 'tc_no'=>'66778899001', 'first_name'=>'Şafak', 'last_name'=>'Gürsoy', 'title'=>'Veznedar', 'department'=>'Mali Hizmetler', 'unit'=>'Tahsilat Birimi', 'phone'=>'0532 100 0025 (D: 1210)', 'email'=>'safak.gursoy@belediye.bel.tr', 'room_no'=>'Ana Giriş Vezneler', 'status'=>'Aktif', 'hire_date'=>'2019-03-01'],
                ['id'=>26, 'sicil_no'=>'SIC-1026', 'tc_no'=>'77889900112', 'first_name'=>'Tarkan', 'last_name'=>'Polat', 'title'=>'Güvenlik Görevlisi', 'department'=>'Destek Hizmetleri', 'unit'=>'Güvenlik Birimi', 'phone'=>'0532 100 0026 (D: 1520)', 'email'=>'tarkan.polat@belediye.bel.tr', 'room_no'=>'Danışma / Güvenlik', 'status'=>'Aktif', 'hire_date'=>'2017-06-15'],
                ['id'=>27, 'sicil_no'=>'SIC-1027', 'tc_no'=>'88990011223', 'first_name'=>'Tuğba', 'last_name'=>'Kara', 'title'=>'Eğitim Danışmanı', 'department'=>'İnsan Kaynakları', 'unit'=>'Hizmetiçi Eğitim', 'phone'=>'0532 100 0027 (D: 1108)', 'email'=>'tugba.kara@belediye.bel.tr', 'room_no'=>'Kat 1 Oda 111', 'status'=>'Aktif', 'hire_date'=>'2023-02-01'],
                ['id'=>28, 'sicil_no'=>'SIC-1028', 'tc_no'=>'99001122334', 'first_name'=>'Uğur', 'last_name'=>'Taş', 'title'=>'BT Saha Destek Uzmanı', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Saha Destek Birimi', 'phone'=>'0532 100 0028 (D: 1003)', 'email'=>'ugur.tas@belediye.bel.tr', 'room_no'=>'Kat 2 Oda 202', 'status'=>'Aktif', 'hire_date'=>'2021-12-01'],
                ['id'=>29, 'sicil_no'=>'SIC-1029', 'tc_no'=>'00112233445', 'first_name'=>'Ümit', 'last_name'=>'Özkan', 'title'=>'Mühendis', 'department'=>'Fen İşleri Müdürlüğü', 'unit'=>'Makine İkmal', 'phone'=>'0532 100 0029 (D: 1420)', 'email'=>'umit.ozkan@belediye.bel.tr', 'room_no'=>'Atölye Binası', 'status'=>'Aktif', 'hire_date'=>'2020-04-15'],
                ['id'=>30, 'sicil_no'=>'SIC-1030', 'tc_no'=>'12233445566', 'first_name'=>'Volkan', 'last_name'=>'Gök', 'title'=>'Grafiker', 'department'=>'Kültür ve Sosyal İşler', 'unit'=>'Basın Yayın', 'phone'=>'0532 100 0030 (D: 1830)', 'email'=>'volkan.gok@belediye.bel.tr', 'room_no'=>'Kültür Binası Kat 2', 'status'=>'Aktif', 'hire_date'=>'2022-09-01'],
                ['id'=>31, 'sicil_no'=>'SIC-1031', 'tc_no'=>'23344556677', 'first_name'=>'Yasemin', 'last_name'=>'Avcı', 'title'=>'Ruhsat Uzmanı', 'department'=>'Zabıta Müdürlüğü', 'unit'=>'Ruhsat Denetim', 'phone'=>'0532 100 0031 (D: 1710)', 'email'=>'yasemin.avci@belediye.bel.tr', 'room_no'=>'Ek Hizmet Binası', 'status'=>'Aktif', 'hire_date'=>'2021-07-15'],
                ['id'=>32, 'sicil_no'=>'SIC-1032', 'tc_no'=>'34455667788', 'first_name'=>'Yavuz', 'last_name'=>'Sert', 'title'=>'İmar Denetim Memuru', 'department'=>'İmar ve Şehircilik Müdürlüğü', 'unit'=>'Yapı Kontrol', 'phone'=>'0532 100 0032 (D: 1315)', 'email'=>'yavuz.sert@belediye.bel.tr', 'room_no'=>'Kat 3 Oda 315', 'status'=>'Aktif', 'hire_date'=>'2019-10-01'],
                ['id'=>33, 'sicil_no'=>'SIC-1033', 'tc_no'=>'45566778899', 'first_name'=>'Zafer', 'last_name'=>'Alkan', 'title'=>'Sistem Yöneticisi', 'department'=>'Bilgi İşlem Müdürlüğü', 'unit'=>'Sistem & Ağ Birimi', 'phone'=>'0532 100 0033 (D: 1005)', 'email'=>'zafer.alkan@belediye.bel.tr', 'room_no'=>'Kat 2 Sistem Odası', 'status'=>'Aktif', 'hire_date'=>'2016-01-10'],
                ['id'=>34, 'sicil_no'=>'SIC-1034', 'tc_no'=>'56677889900', 'first_name'=>'Zehra', 'last_name'=>'Bulut', 'title'=>'Arşiv Görevlisi', 'department'=>'Yazı İşleri Müdürlüğü', 'unit'=>'Kurumsal Arşiv', 'phone'=>'0532 100 0034 (D: 1130)', 'email'=>'zehra.bulut@belediye.bel.tr', 'room_no'=>'Bodrum Kat Arşiv', 'status'=>'Aktif', 'hire_date'=>'2023-03-10'],
                ['id'=>35, 'sicil_no'=>'SIC-1035', 'tc_no'=>'67788990011', 'first_name'=>'Zeki', 'last_name'=>'Candan', 'title'=>'Şoför / Personel Servisi', 'department'=>'Destek Hizmetleri', 'unit'=>'Ulaşım Hizmetleri', 'phone'=>'0532 100 0035 (D: 1530)', 'email'=>'zeki.candan@belediye.bel.tr', 'room_no'=>'Garaj Amirliği', 'status'=>'Aktif', 'hire_date'=>'2018-11-20']
            ];
        }
    }

    /**
     * Get paginated and filtered personnel list
     */
    public function getFilteredPaginated(array $filters = [], int $page = 1, int $perPage = 8): array
    {
        $items = $_SESSION['mock_personnel'];

        foreach ($items as &$person) {
            $person['full_name'] = $person['first_name'] . ' ' . $person['last_name'];
        }

        // Search query filter (search in sicil_no, full_name, email, phone, title)
        if (!empty($filters['search'])) {
            $search = mb_strtolower(trim($filters['search']), 'UTF-8');
            $items = array_filter($items, function ($item) use ($search) {
                return str_contains(mb_strtolower($item['sicil_no'], 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($item['first_name'] . ' ' . $item['last_name'], 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($item['email'], 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($item['phone'], 'UTF-8'), $search) ||
                       str_contains(mb_strtolower($item['title'], 'UTF-8'), $search);
            });
        }

        // Department filter
        if (!empty($filters['department'])) {
            $dept = $filters['department'];
            $items = array_filter($items, fn($item) => $item['department'] === $dept);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $st = $filters['status'];
            $items = array_filter($items, fn($item) => $item['status'] === $st);
        }

        // Apply Turkish multi-key sorting:
        $this->sortPersonnel($items);

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
     * Find single personnel member by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_personnel'] as $p) {
            if ($p['id'] === $id) {
                $p['full_name'] = $p['first_name'] . ' ' . $p['last_name'];
                return $p;
            }
        }
        return null;
    }

    /**
     * Helper to sort personnel records:
     * Priority: 1. Department (ASC) -> 2. Full Name (ASC) -> 3. Sicil No (Natural ASC)
     */
    private function sortPersonnel(array &$items): void
    {
        usort($items, function ($a, $b) {
            $cmpDept = turkish_strcmp($a['department'] ?? '', $b['department'] ?? '');
            if ($cmpDept !== 0) return $cmpDept;

            $nameA = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
            $nameB = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? ''));
            if (empty($nameA)) $nameA = $a['full_name'] ?? $a['name'] ?? '';
            if (empty($nameB)) $nameB = $b['full_name'] ?? $b['name'] ?? '';

            $cmpName = turkish_strcmp($nameA, $nameB);
            if ($cmpName !== 0) return $cmpName;

            return strnatcasecmp($a['sicil_no'] ?? '', $b['sicil_no'] ?? '');
        });
    }

    /**
     * Get all personnel records
     */
    public function getAll(): array
    {
        $items = array_map(function($p) {
            $p['full_name'] = $p['first_name'] . ' ' . $p['last_name'];
            return $p;
        }, $_SESSION['mock_personnel']);

        $this->sortPersonnel($items);
        return $items;
    }

    /**
     * Store new personnel member
     */
    public function store(array $data): array
    {
        $newId = count($_SESSION['mock_personnel']) > 0
            ? max(array_column($_SESSION['mock_personnel'], 'id')) + 1
            : 1;

        $sicilNo = !empty($data['sicil_no']) 
            ? trim($data['sicil_no']) 
            : 'SIC-' . (1000 + $newId);

        $newPerson = [
            'id'          => $newId,
            'sicil_no'    => $sicilNo,
            'tc_no'       => trim($data['tc_no'] ?? '10000000000'),
            'first_name'  => trim($data['first_name'] ?? ''),
            'last_name'   => trim($data['last_name'] ?? ''),
            'title'       => trim($data['title'] ?? 'Memur'),
            'department'  => trim($data['department'] ?? 'Bilgi İşlem Müdürlüğü'),
            'unit'        => trim($data['unit'] ?? 'Genel Birim'),
            'phone'       => trim($data['phone'] ?? '0532 000 0000'),
            'email'       => trim($data['email'] ?? 'personel@belediye.bel.tr'),
            'room_no'     => trim($data['room_no'] ?? 'Kat 1 Oda 101'),
            'status'      => $data['status'] ?? 'Aktif',
            'hire_date'   => $data['hire_date'] ?? date('Y-m-d')
        ];

        array_unshift($_SESSION['mock_personnel'], $newPerson);

        return $newPerson;
    }

    /**
     * Update existing personnel member
     */
    public function update(int $id, array $data): bool
    {
        foreach ($_SESSION['mock_personnel'] as &$p) {
            if ($p['id'] === $id) {
                $p['sicil_no']   = trim($data['sicil_no'] ?? $p['sicil_no']);
                $p['tc_no']      = trim($data['tc_no'] ?? $p['tc_no']);
                $p['first_name'] = trim($data['first_name'] ?? $p['first_name']);
                $p['last_name']  = trim($data['last_name'] ?? $p['last_name']);
                $p['title']      = trim($data['title'] ?? $p['title']);
                $p['department'] = trim($data['department'] ?? $p['department']);
                $p['unit']       = trim($data['unit'] ?? $p['unit']);
                $p['phone']      = trim($data['phone'] ?? $p['phone']);
                $p['email']      = trim($data['email'] ?? $p['email']);
                $p['room_no']    = trim($data['room_no'] ?? $p['room_no']);
                $p['status']     = $data['status'] ?? $p['status'];
                $p['hire_date']  = $data['hire_date'] ?? $p['hire_date'];
                return true;
            }
        }
        return false;
    }

    /**
     * Delete personnel member
     */
    public function delete(int $id): bool
    {
        foreach ($_SESSION['mock_personnel'] as $index => $p) {
            if ($p['id'] === $id) {
                unset($_SESSION['mock_personnel'][$index]);
                $_SESSION['mock_personnel'] = array_values($_SESSION['mock_personnel']);
                return true;
            }
        }
        return false;
    }

    /**
     * Filter options for dropdowns
     */
    public function getFilterOptions(): array
    {
        $departments = array_unique(array_column($_SESSION['mock_personnel'], 'department'));
        sort($departments);

        return [
            'departments' => $departments,
            'statuses'    => ['Aktif', 'Pasif', 'Ayrıldı']
        ];
    }
}
