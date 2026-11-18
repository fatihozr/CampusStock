<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Personnel;
use App\Models\Product;

class ReportController
{
    private Product $productModel;
    private Personnel $personnelModel;

    public function __construct()
    {
        $this->productModel     = new Product();
        $this->personnelModel = new Personnel();
    }

    /**
     * Helper: Get Stocks sorted by Malzeme Adı A -> Z (Turkish collation)
     */
    private function getSortedStocks(): array
    {
        $items = $this->productModel->getAll() ?? [];
        usort($items, function ($a, $b) {
            $cmpName = turkish_strcmp($a['name'] ?? '', $b['name'] ?? '');
            if ($cmpName !== 0) return $cmpName;
            return strnatcasecmp($a['code'] ?? '', $b['code'] ?? '');
        });
        return $items;
    }

    /**
     * Helper: Get Personnel sorted by Ad Soyad A -> Z (Turkish collation)
     */
    private function getSortedPersonnel(): array
    {
        $items = $this->personnelModel->getAll() ?? [];
        usort($items, function ($a, $b) {
            $nameA = $a['full_name'] ?? (($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
            $nameB = $b['full_name'] ?? (($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? ''));
            return turkish_strcmp($nameA, $nameB);
        });
        return $items;
    }

    /**
     * Show Main Interactive System Reports Hub Page inside System Layout
     */
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('reports');

        $activeTab = $_GET['tab'] ?? 'stocks';

        $stocks = $this->getSortedStocks();
        
        // Calculate costs
        $totalCost = 0;
        $categoryCosts = [];
        foreach ($stocks as $stock) {
            $cost = ($stock['current_stock'] ?? 0) * ($stock['unit_price'] ?? 0);
            $totalCost += $cost;
            $cat = $stock['category_name'] ?? 'Genel';
            if (!isset($categoryCosts[$cat])) {
                $categoryCosts[$cat] = 0;
            }
            $categoryCosts[$cat] += $cost;
        }

        $stockMovementModel = new \App\Models\StockMovement();
        
        $filters = [
            'start_date'    => $_GET['start_date'] ?? null,
            'end_date'      => $_GET['end_date'] ?? null,
            'type'          => $_GET['mov_type'] ?? 'all',
            'department_id' => $_GET['department_id'] ?? null,
            'product_id'    => $_GET['product_id'] ?? null,
            'search'        => $_GET['search'] ?? null
        ];
        
        $consumptionData = $stockMovementModel->getConsumptionByDepartment($filters);

        $movementsData = $stockMovementModel->getFilteredMovements($filters);
        $criticalStocks = $this->productModel->getCriticalStock();

        $departmentModel = new \App\Models\Department();
        $departments = method_exists($departmentModel, 'getAllActive') ? $departmentModel->getAllActive() : $departmentModel->getAll();

        view('report.index', [
            'pageTitle'     => 'Sistem Raporları Hub | Stationery Flow E-Ticaret ve Depo',
            'currentUser'   => Auth::user(),
            'activeTab'     => $activeTab,
            'stocks'        => $stocks,
            'departments'   => $departments,
            'filters'       => $filters,
            'personnel'     => $this->getSortedPersonnel(),
            'totalCost'     => $totalCost,
            'categoryCosts' => $categoryCosts,
            'consumption'   => $consumptionData,
            'movements'     => $movementsData,
            'critical'      => $criticalStocks
        ]);
    }

    /**
     * Export Reports Endpoint (CSV or XLSX format)
     */
    public function export(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('reports');

        $type   = $_GET['type'] ?? 'stocks';
        $format = strtolower($_GET['format'] ?? 'xlsx');

        $headers  = [];
        $rows     = [];
        $title    = 'Rapor';
        $filename = "Sistem_Raporu_" . date('Y-m-d') . ".{$format}";

        switch ($type) {
            case 'personnel':
                $title    = 'Personel Rehberi Raporu';
                $filename = "Personel_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Sicil No', 'Ad Soyad', 'Unvan', 'Müdürlük', 'Birim', 'E-Posta', 'Telefon', 'Durum'];
                $items    = $this->getSortedPersonnel();
                foreach ($items as $item) {
                    $rows[] = [
                        $item['sicil_no'] ?? '',
                        $item['full_name'] ?? $item['name'] ?? '',
                        $item['title'] ?? '',
                        $item['department'] ?? '',
                        $item['unit'] ?? '',
                        $item['email'] ?? '',
                        $item['phone'] ?? '',
                        $item['status'] ?? ''
                    ];
                }
                break;

            case 'costs':
                $title    = 'Toplam Maliyet Raporu';
                $filename = "Maliyet_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Birim Fiyat (TL)', 'Toplam Maliyet (TL)'];
                $items    = $this->getSortedStocks();
                if (!empty($_GET['product_id']) && $_GET['product_id'] !== 'all') {
                    $items = array_filter($items, fn($i) => $i['id'] == $_GET['product_id']);
                }
                foreach ($items as $item) {
                    $total = (int)($item['current_stock'] ?? 0) * (float)($item['unit_price'] ?? 0);
                    $rows[] = [
                        $item['code'] ?? '',
                        $item['name'] ?? '',
                        $item['category_name'] ?? 'Genel',
                        (int)($item['current_stock'] ?? 0),
                        $item['unit'] ?? '',
                        (float)($item['unit_price'] ?? 0),
                        $total
                    ];
                }
                break;

            case 'consumption':
                $title    = 'Birim Tüketim Raporu';
                $filename = "Birim_Tuketim_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Birim/Müdürlük', 'Stok Kodu', 'Malzeme Adı', 'Birim', 'Toplam Tüketim', 'Toplam Maliyet'];
                $stockMovementModel = new \App\Models\StockMovement();
                
                $filters = [
                    'start_date'    => $_GET['start_date'] ?? null,
                    'end_date'      => $_GET['end_date'] ?? null,
                    'type'          => $_GET['mov_type'] ?? 'all',
                    'department_id' => $_GET['department_id'] ?? null,
                    'product_id'    => $_GET['product_id'] ?? null,
                    'search'        => $_GET['search'] ?? null
                ];
                $items    = $stockMovementModel->getConsumptionByDepartment($filters);
                foreach ($items as $item) {
                    $rows[] = [
                        $item['department_name'] ?? 'Bilinmiyor',
                        $item['product_code'] ?? '',
                        $item['product_name'] ?? '',
                        $item['unit'] ?? '',
                        (int)($item['total_quantity'] ?? 0),
                        (float)($item['total_cost'] ?? 0)
                    ];
                }
                break;

            case 'critical':
                $title    = 'Kritik Stok Uyarısı Raporu';
                $filename = "Kritik_Stok_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Kritik Eşik', 'Durum'];
                $items    = $this->productModel->getCriticalStock();
                if (!empty($_GET['product_id']) && $_GET['product_id'] !== 'all') {
                    $items = array_filter($items, fn($i) => $i['id'] == $_GET['product_id']);
                }
                foreach ($items as $item) {
                    $fark = (int)($item['min_stock'] ?? 0) - (int)($item['current_stock'] ?? 0);
                    $rows[] = [
                        $item['code'] ?? '',
                        $item['name'] ?? '',
                        $item['category_name'] ?? 'Genel',
                        (int)($item['current_stock'] ?? 0),
                        $item['unit'] ?? '',
                        (int)($item['min_stock'] ?? 0),
                        "-$fark Eksik"
                    ];
                }
                break;

            case 'movements':
                $title    = 'Çıkış Hareketleri Ekstresi';
                $filename = "Cikis_Hareketleri_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Tarih', 'İşlem Tipi', 'Malzeme Adı', 'Miktar', 'Birim', 'Teslim Eden', 'Teslim Alan Daire Bşk.'];
                $stockMovementModel = new \App\Models\StockMovement();
                
                $filters = [
                    'start_date'    => $_GET['start_date'] ?? null,
                    'end_date'      => $_GET['end_date'] ?? null,
                    'type'          => $_GET['mov_type'] ?? 'all',
                    'department_id' => $_GET['department_id'] ?? null,
                    'product_id'    => $_GET['product_id'] ?? null,
                    'search'        => $_GET['search'] ?? null
                ];
                $items    = $stockMovementModel->getFilteredMovements($filters);
                
                foreach ($items as $item) {
                    $performedBy = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));
                    $deptName = $item['department_name'] ?? '-';
                    $tip = ($item['type'] === 'in') ? 'GİRİŞ' : 'ÇIKIŞ';
                    $date = date('d.m.Y H:i', strtotime($item['created_at']));
                    
                    if ($item['type'] === 'in') {
                        $teslimEden = '-';
                        $teslimAlan = $performedBy;
                    } else {
                        $teslimEden = $performedBy;
                        $teslimAlan = $deptName;
                    }

                    $rows[] = [
                        $date,
                        $tip,
                        $item['product_name'] ?? '',
                        (int)($item['quantity'] ?? 0),
                        $item['unit'] ?? '',
                        $teslimEden,
                        $teslimAlan
                    ];
                }
                break;

            case 'stocks':
            default:
                $type     = 'stocks';
                $title    = 'Stok Durum Raporu';
                $filename = "Stok_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Kritik Eşik', 'Birim Fiyat (TL)'];
                $items    = $this->getSortedStocks();
                if (!empty($_GET['product_id']) && $_GET['product_id'] !== 'all') {
                    $items = array_filter($items, fn($i) => $i['id'] == $_GET['product_id']);
                }
                foreach ($items as $item) {
                    $rows[] = [
                        $item['code'] ?? '',
                        $item['name'] ?? '',
                        $item['category_name'] ?? 'Genel',
                        (int)($item['current_stock'] ?? 0),
                        $item['unit'] ?? '',
                        (int)($item['min_stock'] ?? 0),
                        (float)($item['unit_price'] ?? 0)
                    ];
                }
                break;
        }

        // XLSX format using XlsxWriter
        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
        }

        $xlsx = new \App\Services\XlsxWriter($title);
        $xlsx->setHeaders($headers);
        foreach ($rows as $row) {
            $xlsx->addRow($row);
        }

        echo $xlsx->build();
        exit;
    }

    /**
     * Printable Official PDF Report Endpoint
     */
    public function pdf(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('reports');

        $module = $_GET['type'] ?? 'stocks';

        $reportTitle = 'DEPO SARF STOK SEVİYE RAPORU';
        $stocks      = $this->getSortedStocks();
        $personnel   = $this->getSortedPersonnel();
        $reportData  = $stocks;

        switch ($module) {
            case 'personnel':
                $reportTitle = 'BELEDİYE PERSONEL REHBER İCMAL RAPORU';
                $reportData  = $personnel;
                break;
            case 'costs':
                $reportTitle = 'DEPO TOPLAM MALİYET RAPORU';
                $reportData  = $stocks;
                if (!empty($_GET['product_id']) && $_GET['product_id'] !== 'all') {
                    $reportData = array_filter($reportData, fn($i) => $i['id'] == $_GET['product_id']);
                }
                break;
            case 'consumption':
                $reportTitle = 'BİRİM BAZLI TÜKETİM RAPORU';
                $stockMovementModel = new \App\Models\StockMovement();
                $filters = [
                    'start_date'    => $_GET['start_date'] ?? null,
                    'end_date'      => $_GET['end_date'] ?? null,
                    'type'          => $_GET['mov_type'] ?? 'all',
                    'department_id' => $_GET['department_id'] ?? null,
                    'product_id'    => $_GET['product_id'] ?? null,
                    'search'        => $_GET['search'] ?? null
                ];
                $reportData  = $stockMovementModel->getConsumptionByDepartment($filters);
                break;
            case 'critical':
                $reportTitle = 'KRİTİK STOK UYARISI RAPORU (ACİL)';
                $reportData  = $this->productModel->getCriticalStock();
                if (!empty($_GET['product_id']) && $_GET['product_id'] !== 'all') {
                    $reportData = array_filter($reportData, fn($i) => $i['id'] == $_GET['product_id']);
                }
                break;
            case 'movements':
                $reportTitle = 'STOK HAREKETLERİ EKSTRESİ RAPORU';
                $stockMovementModel = new \App\Models\StockMovement();
                $filters = [
                    'start_date'    => $_GET['start_date'] ?? null,
                    'end_date'      => $_GET['end_date'] ?? null,
                    'type'          => $_GET['mov_type'] ?? 'all',
                    'department_id' => $_GET['department_id'] ?? null,
                    'product_id'    => $_GET['product_id'] ?? null,
                    'search'        => $_GET['search'] ?? null
                ];
                $reportData  = $stockMovementModel->getFilteredMovements($filters);
                break;
            case 'stocks':
            default:
                $module      = 'stocks';
                $reportTitle = 'DEPO SARF STOK SEVİYE RAPORU';
                $reportData  = $stocks;
                if (!empty($_GET['product_id']) && $_GET['product_id'] !== 'all') {
                    $reportData = array_filter($reportData, fn($i) => $i['id'] == $_GET['product_id']);
                }
                break;
        }

        $currentUser = Auth::user() ?? [
            'first_name' => 'Sistem',
            'last_name'  => 'Yöneticisi',
            'title'      => 'Sorumlu'
        ];

        view('report.print', [
            'pageTitle'   => $reportTitle,
            'reportTitle' => $reportTitle,
            'title'       => $reportTitle,
            'module'      => $module,
            'type'        => $module,
            'reportData'  => $reportData,
            'stocks'      => $stocks,
            'personnel'   => $personnel,
            'currentUser' => $currentUser,
            'generatedAt' => date('d.m.Y H:i')
        ]);
    }

    /**
     * Send Report via Email AJAX Endpoint (PDF, XLSX attachment)
     */
    public function sendEmail(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('reports');

        header('Content-Type: application/json; charset=utf-8');

        $toEmail  = trim($_POST['to_email'] ?? '');
        $ccEmail  = trim($_POST['cc_email'] ?? '');
        $bccEmail = trim($_POST['bcc_email'] ?? '');
        $subject  = trim($_POST['subject'] ?? '');
        $message  = trim($_POST['message'] ?? '');
        $format   = strtolower(trim($_POST['format'] ?? 'pdf'));
        $type     = strtolower(trim($_POST['type'] ?? 'stocks'));

        if (empty($toEmail) || empty($subject)) {
            echo json_encode(['success' => false, 'code' => 'INVALID_INPUT', 'message' => 'Lütfen alıcı e-posta adresini ve konuyu giriniz!']);
            exit;
        }

        $items   = [];
        $title   = 'Rapor';
        $headers = [];
        $rows    = [];

        $searchFilter     = strtolower(trim($_POST['search'] ?? $_GET['search'] ?? ''));
        $categoryFilter   = trim($_POST['category'] ?? $_GET['category'] ?? '');
        $departmentFilter = trim($_POST['department'] ?? $_GET['department'] ?? '');
        $warehouseFilter  = trim($_POST['warehouse'] ?? $_GET['warehouse'] ?? '');

        switch ($type) {
            case 'personnel':
                $title   = 'Personel Raporu';
                $headers = ['Sicil No', 'Ad Soyad', 'Unvan', 'Müdürlük', 'Birim', 'E-Posta', 'Telefon', 'Durum'];
                $items   = $this->getSortedPersonnel();
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['full_name'] ?? '').' '.($i['sicil_no'] ?? '')), $searchFilter)));
                }
                if ($departmentFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => ($i['department'] ?? '') === $departmentFilter));
                }
                foreach ($items as $item) {
                    $rows[] = [
                        $item['sicil_no'] ?? '', $item['full_name'] ?? $item['name'] ?? '', $item['title'] ?? '',
                        $item['department'] ?? '', $item['unit'] ?? '', $item['email'] ?? '', $item['phone'] ?? '', $item['status'] ?? ''
                    ];
                }
                break;

            case 'costs':
                $title    = 'Maliyet Raporu';
                $headers  = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Birim Fiyat (TL)', 'Toplam Maliyet (TL)'];
                $items    = $this->getSortedStocks();
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['name'] ?? '').' '.($i['code'] ?? '')), $searchFilter)));
                }
                foreach ($items as $item) {
                    $total = (int)($item['current_stock'] ?? 0) * (float)($item['unit_price'] ?? 0);
                    $rows[] = [
                        $item['code'] ?? '', $item['name'] ?? '', $item['category_name'] ?? 'Genel', (int)($item['current_stock'] ?? 0),
                        $item['unit'] ?? '', (float)($item['unit_price'] ?? 0), $total
                    ];
                }
                break;

            case 'consumption':
                $title   = 'Birim Tüketim Raporu';
                $headers = ['Birim/Müdürlük', 'Stok Kodu', 'Malzeme Adı', 'Birim', 'Toplam Tüketim', 'Toplam Maliyet'];
                $stockMovementModel = new \App\Models\StockMovement();
                $mFilters = [
                    'start_date'    => $_POST['start_date'] ?? $_GET['start_date'] ?? null,
                    'end_date'      => $_POST['end_date'] ?? $_GET['end_date'] ?? null,
                    'type'          => $_POST['mov_type'] ?? $_GET['mov_type'] ?? 'all',
                    'department_id' => $_POST['department_id'] ?? $_GET['department_id'] ?? null,
                    'product_id'    => $_POST['product_id'] ?? $_GET['product_id'] ?? null,
                    'search'        => $searchFilter
                ];
                $items   = $stockMovementModel->getConsumptionByDepartment($mFilters);
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['department_name'] ?? '').' '.($i['product_name'] ?? '')), $searchFilter)));
                }
                foreach ($items as $item) {
                    $rows[] = [
                        $item['department_name'] ?? 'Bilinmiyor', $item['product_code'] ?? '', $item['product_name'] ?? '',
                        $item['unit'] ?? '', (int)($item['total_quantity'] ?? 0), (float)($item['total_cost'] ?? 0)
                    ];
                }
                break;

            case 'critical':
                $title   = 'Kritik Stok Uyarısı Raporu';
                $headers = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Kritik Eşik'];
                $items   = $this->productModel->getCriticalStock();
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['name'] ?? '').' '.($i['code'] ?? '')), $searchFilter)));
                }
                foreach ($items as $item) {
                    $rows[] = [
                        $item['code'] ?? '', $item['name'] ?? '', $item['category_name'] ?? 'Genel', (int)($item['current_stock'] ?? 0),
                        $item['unit'] ?? '', (int)($item['min_stock'] ?? 0)
                    ];
                }
                break;

            case 'movements':
                $title   = 'Stok Hareketleri Ekstresi';
                $headers = ['Tarih', 'İşlem Tipi', 'Stok Kodu', 'Malzeme Adı', 'Miktar', 'Birim', 'Teslim Eden', 'Teslim Alan Daire Bşk.', 'Durum'];
                $stockMovementModel = new \App\Models\StockMovement();
                $mFilters = [
                    'start_date'    => $_POST['start_date'] ?? $_GET['start_date'] ?? null,
                    'end_date'      => $_POST['end_date'] ?? $_GET['end_date'] ?? null,
                    'type'          => $_POST['mov_type'] ?? $_GET['mov_type'] ?? 'all',
                    'department_id' => $_POST['department_id'] ?? $_GET['department_id'] ?? null,
                    'product_id'    => $_POST['product_id'] ?? $_GET['product_id'] ?? null,
                    'search'        => $searchFilter
                ];
                $items   = $stockMovementModel->getFilteredMovements($mFilters);
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['product_name'] ?? '').' '.($i['department_name'] ?? '')), $searchFilter)));
                }
                foreach ($items as $item) {
                    $performedBy = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));
                    $deptName = $item['department_name'] ?? '-';
                    $tip = ($item['type'] === 'in') ? 'GİRİŞ' : 'ÇIKIŞ';
                    $date = date('d.m.Y H:i', strtotime($item['created_at']));
                    
                    if ($item['type'] === 'in') {
                        $teslimEden = '-';
                        $teslimAlan = $performedBy;
                    } else {
                        $teslimEden = $performedBy;
                        $teslimAlan = $deptName;
                    }

                    $rows[] = [
                        $date,
                        $tip,
                        $item['product_code'] ?? '',
                        $item['product_name'] ?? '',
                        (int)($item['quantity'] ?? 0),
                        $item['unit'] ?? '',
                        $teslimEden,
                        $teslimAlan,
                        $item['status'] ?? ''
                    ];
                }
                break;

            case 'stocks':
            default:
                $type    = 'stocks';
                $title   = 'Stok Raporu';
                $headers = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Kritik Eşik', 'Birim Fiyat (TL)'];
                $items   = $this->getSortedStocks();
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['name'] ?? '').' '.($i['code'] ?? '')), $searchFilter)));
                }
                foreach ($items as $item) {
                    $rows[] = [
                        $item['code'] ?? '', $item['name'] ?? '', $item['category_name'] ?? 'Genel', (int)($item['current_stock'] ?? 0),
                        $item['unit'] ?? '', (int)($item['min_stock'] ?? 0), (float)($item['unit_price'] ?? 0)
                    ];
                }
                break;
        }

        // Generate temporary attachment file
        $tempDir  = sys_get_temp_dir();
        $safeTitle= str_replace(' ', '_', $title);
        $filename = "{$safeTitle}_" . date('Y-m-d') . ".{$format}";
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'rpt_' . md5(uniqid()) . ".{$format}";

        if ($format === 'xlsx') {
            $xlsx = new \App\Services\XlsxWriter($title);
            $xlsx->setHeaders($headers);
            foreach ($rows as $row) {
                $xlsx->addRow($row);
            }
            file_put_contents($tempPath, $xlsx->build());
        } else {
            ob_start();
            $currentUser = Auth::user() ?? ['first_name' => 'Sistem', 'last_name' => 'Yöneticisi'];
            view('report.print', [
                'pageTitle'   => $title,
                'reportTitle' => $title,
                'title'       => $title,
                'module'      => $type,
                'type'        => $type,
                'reportData'  => $items,
                'stocks'      => $items,
                'personnel'   => $items,
                'currentUser' => $currentUser,
                'generatedAt' => date('d.m.Y H:i')
            ]);
            $pdfHtml = ob_get_clean();
            file_put_contents($tempPath, $pdfHtml);
            $filename = "{$safeTitle}_" . date('Y-m-d') . ".html";
        }

        $mailService = new \App\Services\MailService();
        $res = $mailService->sendReportMail([
            'to'              => $toEmail,
            'cc'              => $ccEmail,
            'bcc'             => $bccEmail,
            'subject'         => $subject,
            'body'            => $message,
            'attachment_path' => $tempPath,
            'attachment_name' => $filename
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        echo json_encode($res);
        exit;
    }
}
