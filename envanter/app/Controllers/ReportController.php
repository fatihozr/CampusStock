<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Personnel;
use App\Models\Stock;

class ReportController
{
    private Stock $stockModel;
    private Personnel $personnelModel;

    public function __construct()
    {
        $this->stockModel     = new Stock();
        $this->personnelModel = new Personnel();
    }

    /**
     * Helper: Get Stocks sorted by Malzeme Adı A -> Z (Turkish collation)
     */
    private function getSortedStocks(): array
    {
        $items = $this->stockModel->getAllItems() ?? [];
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

        view('report.index', [
            'pageTitle'   => 'Sistem Raporları Hub | Belediye Envanter Takip Sistemi',
            'currentUser' => Auth::user(),
            'activeTab'   => $activeTab,
            'stocks'      => $this->getSortedStocks(),
            'personnel'   => $this->getSortedPersonnel()
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
        $filename = "Belediye_Raporu_" . date('Y-m-d') . ".{$format}";

        switch ($type) {
            case 'personnel':
                $title    = 'Personel Rehberi Raporu';
                $filename = "Belediye_Personel_Raporu_" . date('Y-m-d') . ".{$format}";
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

            case 'stocks':
            default:
                $type     = 'stocks';
                $title    = 'Stok Durum Raporu';
                $filename = "Belediye_Stok_Raporu_" . date('Y-m-d') . ".{$format}";
                $headers  = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Kritik Eşik', 'Depo', 'Raf / Lokasyon', 'Birim Fiyat (TL)'];
                $items    = $this->getSortedStocks();
                foreach ($items as $item) {
                    $rows[] = [
                        $item['code'] ?? '',
                        $item['name'] ?? '',
                        $item['category'] ?? '',
                        (int)($item['quantity'] ?? 0),
                        $item['unit'] ?? '',
                        (int)($item['min_threshold'] ?? 0),
                        $item['warehouse'] ?? '',
                        $item['rack_location'] ?? 'C31',
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
            case 'stocks':
            default:
                $module      = 'stocks';
                $reportTitle = 'DEPO SARF STOK SEVİYE RAPORU';
                $reportData  = $stocks;
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

            case 'stocks':
            default:
                $type    = 'stocks';
                $title   = 'Stok Raporu';
                $headers = ['Stok Kodu', 'Malzeme Adı', 'Kategori', 'Mevcut Miktar', 'Birim', 'Kritik Eşik', 'Depo', 'Raf / Lokasyon', 'Birim Fiyat (TL)'];
                $items   = $this->getSortedStocks();
                if ($searchFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => str_contains(strtolower(($i['name'] ?? '').' '.($i['code'] ?? '').' '.($i['rack_location'] ?? '')), $searchFilter)));
                }
                if ($categoryFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => ($i['category'] ?? '') === $categoryFilter));
                }
                if ($warehouseFilter !== '') {
                    $items = array_values(array_filter($items, fn($i) => ($i['warehouse'] ?? '') === $warehouseFilter));
                }
                foreach ($items as $item) {
                    $rows[] = [
                        $item['code'] ?? '', $item['name'] ?? '', $item['category'] ?? '', (int)($item['quantity'] ?? 0),
                        $item['unit'] ?? '', (int)($item['min_threshold'] ?? 0), $item['warehouse'] ?? '',
                        $item['rack_location'] ?? 'C31', (float)($item['unit_price'] ?? 0)
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
