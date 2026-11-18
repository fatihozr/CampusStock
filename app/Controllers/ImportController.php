<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\ExcelParser;
use App\Models\Import;
use Exception;

class ImportController
{
    private Import $importModel;

    public function __construct()
    {
        $this->importModel = new Import();
    }

    /**
     * Step 1: Show Excel Import Wizard Main Screen
     */
    public function index(): void
    {
        Auth::requirePermission('inventory');

        // Reset wizard session data
        unset($_SESSION['import_wizard']);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('import.index', [
            'pageTitle'    => 'Excel\'den Toplu Veri Aktarımı | Stationery Flow E-Ticaret ve Depo',
            'currentUser'  => Auth::user(),
            'step'         => 1,
            'flashSuccess' => $flashSuccess,
            'flashError'   => $flashError
        ]);
    }

    /**
     * Handle File Upload, Auto Column Match & Perform Instant Row Validation
     */
    public function upload(): void
    {
        Auth::requirePermission('inventory');

        $type = $_POST['import_type'] ?? 'stock';

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Lütfen geçerli bir Excel (.xlsx, .xls) veya CSV dosyası yükleyiniz!';
            redirect('/import');
            return;
        }

        $fileTmpPath = $_FILES['excel_file']['tmp_name'];
        $fileName    = $_FILES['excel_file']['name'];

        try {
            $parsed = ExcelParser::parseFile($fileTmpPath);
            $headers = $parsed['headers'];
            $rows    = $parsed['rows'];
            $total   = $parsed['total'];

            $targetFields = Import::getTargetFields($type);

            // Auto-guess column mappings for all target fields
            $userMapping = [];
            foreach ($targetFields as $fieldKey => $fieldLabel) {
                $userMapping[$fieldKey] = Import::guessMapping($fieldKey, $headers);
            }

            // Perform instant row validation
            $validationResult = $this->importModel->validateAndMapRows($type, $headers, $rows, $userMapping);

            $_SESSION['import_wizard'] = [
                'type'             => $type,
                'file_name'        => $fileName,
                'headers'          => $headers,
                'rows'             => $rows,
                'total'            => $total,
                'user_mapping'     => $userMapping,
                'validation'       => $validationResult
            ];

            view('import.index', [
                'pageTitle'        => 'Excel Analiz ve Aktarım | Stationery Flow',
                'currentUser'      => Auth::user(),
                'step'             => 2,
                'type'             => $type,
                'fileName'         => $fileName,
                'headers'          => $headers,
                'totalRows'        => $total,
                'targetFields'     => $targetFields,
                'userMapping'      => $userMapping,
                'validationResult' => $validationResult
            ]);
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Dosya okuma hatası: ' . $e->getMessage();
            redirect('/import');
        }
    }

    /**
     * Handle Manual Column Re-Mapping Adjustment & Update Validation Preview
     */
    public function preview(): void
    {
        Auth::requirePermission('inventory');

        if (!isset($_SESSION['import_wizard'])) {
            $_SESSION['flash_error'] = 'Aktarım oturumu zaman aşımına uğradı. Lütfen dosyayı tekrar yükleyin.';
            redirect('/import');
            return;
        }

        $wizardData   = $_SESSION['import_wizard'];
        $type         = $wizardData['type'];
        $headers      = $wizardData['headers'];
        $rows         = $wizardData['rows'];
        $userMapping  = $_POST['mapping'] ?? ($wizardData['user_mapping'] ?? []);

        $targetFields = Import::getTargetFields($type);
        $validationResult = $this->importModel->validateAndMapRows($type, $headers, $rows, $userMapping);

        $_SESSION['import_wizard']['validation']   = $validationResult;
        $_SESSION['import_wizard']['user_mapping'] = $userMapping;

        view('import.index', [
            'pageTitle'        => 'Excel Analiz ve Aktarım | Stationery Flow',
            'currentUser'      => Auth::user(),
            'step'             => 2,
            'type'             => $type,
            'fileName'         => $wizardData['file_name'],
            'headers'          => $headers,
            'totalRows'        => count($rows),
            'targetFields'     => $targetFields,
            'userMapping'      => $userMapping,
            'validationResult' => $validationResult
        ]);
    }

    /**
     * Step 4: Execute Import of Valid Rows & Show Execution Summary Report
     */
    public function process(): void
    {
        Auth::requirePermission('inventory');

        if (!isset($_SESSION['import_wizard']['validation'])) {
            $_SESSION['flash_error'] = 'Aktarım oturumu bulunamadı. Lütfen işlemi baştan başlatınız.';
            redirect('/import');
            return;
        }

        $wizardData = $_SESSION['import_wizard'];
        $type       = $wizardData['type'];
        $validRows  = $wizardData['validation']['valid'];
        $invalidCount = count($wizardData['validation']['invalid']);

        $importedCount = $this->importModel->executeImport($type, $validRows);

        unset($_SESSION['import_wizard']);

        view('import.index', [
            'pageTitle'     => 'Aktarım Tamamlandı | Excel İçe Aktarma',
            'currentUser'   => Auth::user(),
            'step'          => 4,
            'type'          => $type,
            'importedCount' => $importedCount,
            'invalidCount'  => $invalidCount
        ]);
    }

    /**
     * Download Sample CSV Template File
     */
    public function template(): void
    {
        Auth::requirePermission('inventory');

        $type   = $_GET['type'] ?? 'stock';
        $format = strtolower($_GET['format'] ?? 'xlsx');
        ExcelParser::generateSampleTemplate($type, $format);
    }
}
