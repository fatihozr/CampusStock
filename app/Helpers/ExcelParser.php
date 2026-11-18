<?php

namespace App\Helpers;

use Exception;
use App\Services\XlsxWriter;
use ZipArchive;

class ExcelParser
{
    /**
     * Parse CSV / Excel text or binary OpenXML file and return headers & rows array
     */
    public static function parseFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception('Yüklenen dosya bulunamadı!');
        }

        $content = file_get_contents($filePath);
        if ($content === false || empty(trim($content))) {
            throw new Exception('Yüklenen dosya boş veya okunamadı!');
        }

        // Check if uploaded file is binary OpenXML XLSX ZIP archive
        if (substr($content, 0, 4) === "\x50\x4b\x03\x04") {
            return self::parseXlsxBinary($filePath, $content);
        }

        // CSV parsing
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        $delimiter = ';';
        if (substr_count($content, ',') > substr_count($content, ';')) {
            $delimiter = ',';
        } elseif (substr_count($content, "\t") > substr_count($content, ';')) {
            $delimiter = "\t";
        }

        $lines = explode("\n", str_replace("\r", "", trim($content)));
        if (count($lines) < 2) {
            throw new Exception('Dosyada en az 1 başlık ve 1 veri satırı bulunmalıdır!');
        }

        // Remove sep=; header directive if present
        if (str_starts_with(trim($lines[0]), 'sep=')) {
            array_shift($lines);
        }

        if (count($lines) < 2) {
            throw new Exception('Dosyada en az 1 başlık ve 1 veri satırı bulunmalıdır!');
        }

        $headers = str_getcsv($lines[0], $delimiter);
        $headers = array_map('trim', $headers);

        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $row = str_getcsv($line, $delimiter);
            $hasData = false;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) continue;

            while (count($row) < count($headers)) {
                $row[] = '';
            }
            $rows[] = array_map('trim', array_slice($row, 0, count($headers)));
        }

        if (empty($rows)) {
            throw new Exception('Yüklenen dosyada doldurulmuş veri satırı bulunamadı!');
        }

        return [
            'delimiter' => $delimiter,
            'headers'   => $headers,
            'rows'      => $rows,
            'total'     => count($rows)
        ];
    }

    /**
     * Robust Pure PHP OpenXML XLSX Parser
     */
    private static function parseXlsxBinary(string $filePath, string $binaryData): array
    {
        $files = [];

        // Method 1: Native PHP ZipArchive (100% reliable for all Excel, Sheets, LibreOffice files)
        if (class_exists('\ZipArchive') && file_exists($filePath)) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if ($stat && !empty($stat['name'])) {
                        $files[$stat['name']] = $zip->getFromIndex($i);
                    }
                }
                $zip->close();
            }
        }

        // Method 2: Manual ZIP extraction fallback if ZipArchive missing or returned no files
        if (empty($files)) {
            $offset = 0;
            $len = strlen($binaryData);

            while ($offset < $len) {
                if (substr($binaryData, $offset, 4) !== "\x50\x4b\x03\x04") {
                    break;
                }
                $compMethod  = unpack('v', substr($binaryData, $offset + 8, 2))[1];
                $cSize       = unpack('V', substr($binaryData, $offset + 18, 4))[1];
                $fnLen       = unpack('v', substr($binaryData, $offset + 26, 2))[1];
                $extraLen    = unpack('v', substr($binaryData, $offset + 28, 2))[1];

                $filename    = substr($binaryData, $offset + 30, $fnLen);
                $dataOffset  = $offset + 30 + $fnLen + $extraLen;
                $compressed  = substr($binaryData, $dataOffset, $cSize);

                if ($compMethod === 8) {
                    $uncompressed = @gzinflate($compressed);
                    if ($uncompressed === false) {
                        $uncompressed = @gzuncompress($compressed);
                    }
                } else {
                    $uncompressed = $compressed;
                }

                if ($uncompressed !== false) {
                    $files[$filename] = $uncompressed;
                }
                $offset = $dataOffset + $cSize;
            }
        }

        // Extract Shared Strings
        $sharedStrings = [];
        if (isset($files['xl/sharedStrings.xml'])) {
            $xml = @simplexml_load_string($files['xl/sharedStrings.xml']);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    $val = '';
                    if (isset($si->t)) {
                        $val = (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $val .= (string)($r->t ?? '');
                        }
                    }
                    $sharedStrings[] = trim($val);
                }
            }
        }

        // Find Worksheet XML
        $sheetXmlStr = null;
        if (isset($files['xl/worksheets/sheet1.xml'])) {
            $sheetXmlStr = $files['xl/worksheets/sheet1.xml'];
        } else {
            foreach ($files as $fn => $content) {
                if (str_starts_with($fn, 'xl/worksheets/sheet') && str_ends_with($fn, '.xml')) {
                    $sheetXmlStr = $content;
                    break;
                }
            }
        }

        if (!$sheetXmlStr) {
            throw new Exception('Yüklenen Excel (.xlsx) dosyasında çalışma sayfası okunamadı!');
        }

        $xml = @simplexml_load_string($sheetXmlStr);
        if (!$xml || !isset($xml->sheetData->row)) {
            throw new Exception('Yüklenen Excel (.xlsx) dosyasında veri satırı bulunamadı!');
        }

        $allRows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $rowCells = [];
            foreach ($rowNode->c as $cNode) {
                $cellRef = (string)($cNode['r'] ?? '');
                $colIdx  = self::getColumnIndexFromRef($cellRef);

                $cellValue = self::extractCellValue($cNode, $sharedStrings);

                if ($colIdx >= 0) {
                    // Align cell to its exact column index
                    while (count($rowCells) < $colIdx) {
                        $rowCells[] = '';
                    }
                    $rowCells[$colIdx] = trim($cellValue);
                } else {
                    $rowCells[] = trim($cellValue);
                }
            }
            $allRows[] = $rowCells;
        }

        if (empty($allRows)) {
            throw new Exception('Yüklenen Excel (.xlsx) dosyasında hiçbir veri okunamadı!');
        }

        // Header Row 1
        $headers = array_shift($allRows);
        if (empty($headers)) {
            throw new Exception('Yüklenen Excel (.xlsx) dosyasında sütun başlığı bulunamadı!');
        }

        // Clean header strings
        $headers = array_map('trim', $headers);

        // Filter and align data rows
        $rows = [];
        foreach ($allRows as $r) {
            $hasData = false;
            foreach ($r as $c) {
                if ($c !== '') {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                while (count($r) < count($headers)) {
                    $r[] = '';
                }
                $rows[] = array_slice($r, 0, count($headers));
            }
        }

        if (empty($rows)) {
            throw new Exception('Yüklenen Excel (.xlsx) dosyasında doldurulmuş veri satırı bulunamadı!');
        }

        return [
            'delimiter' => 'xlsx',
            'headers'   => $headers,
            'rows'      => $rows,
            'total'     => count($rows)
        ];
    }

    /**
     * Convert cell reference like "A1", "C2", "AA1" to 0-indexed column integer
     */
    private static function getColumnIndexFromRef(string $cellRef): int
    {
        if (preg_match('/^([A-Z]+)/i', $cellRef, $m)) {
            $colStr = strtoupper($m[1]);
            $len = strlen($colStr);
            $colIdx = 0;
            for ($i = 0; $i < $len; $i++) {
                $colIdx = $colIdx * 26 + (ord($colStr[$i]) - 64);
            }
            return $colIdx - 1;
        }
        return -1;
    }

    /**
     * Extract string value from cell node handling all OpenXML formats
     */
    private static function extractCellValue($cNode, array $sharedStrings): string
    {
        $t = (string)($cNode['t'] ?? '');

        // 1. Shared String (t="s")
        if ($t === 's') {
            $v = (string)($cNode->v ?? '');
            if ($v !== '' && isset($sharedStrings[(int)$v])) {
                return $sharedStrings[(int)$v];
            }
            return '';
        }

        // 2. Inline String (t="inlineStr")
        if ($t === 'inlineStr') {
            if (isset($cNode->is->t)) {
                return (string)$cNode->is->t;
            } elseif (isset($cNode->is->r)) {
                $str = '';
                foreach ($cNode->is->r as $r) {
                    $str .= (string)($r->t ?? '');
                }
                return $str;
            }
        }

        // 3. Formula / Direct text / Value (t="str", t="b", t="n", etc.)
        if (isset($cNode->v)) {
            return (string)$cNode->v;
        }
        if (isset($cNode->is->t)) {
            return (string)$cNode->is->t;
        }

        return '';
    }

    /**
     * Helper to parse uploaded $_FILES file array into associative row maps
     */
    public static function parseUploadedFile(array $fileArray): array
    {
        $tmpPath = $fileArray['tmp_name'] ?? '';
        if (empty($tmpPath) || !file_exists($tmpPath)) {
            throw new Exception('Yüklenen dosya bulunamadı!');
        }

        $parsed = self::parseFile($tmpPath);
        $headers = $parsed['headers'];
        $rows    = $parsed['rows'];

        $rowMaps = [];
        foreach ($rows as $row) {
            $map = [];
            foreach ($headers as $colIdx => $headerName) {
                $cellVal = $row[$colIdx] ?? '';
                $map[$headerName] = $cellVal;

                $norm = \App\Models\Import::normalizeHeaderString((string)$headerName);
                if (in_array($norm, ['stokkodu', 'malzemekodu', 'stokno', 'kodu', 'code'], true)) $map['code'] = $cellVal;
                if (in_array($norm, ['malzemeadi', 'cihazadi', 'cihazmalzemeadi', 'cihaz', 'urunadi', 'malzeme'], true)) $map['name'] = $cellVal;
                if (in_array($norm, ['kategori', 'kategorisi', 'tur', 'cihazturu'], true)) $map['category'] = $cellVal;
                if (in_array($norm, ['birim', 'birimi'], true)) $map['unit'] = $cellVal;
                if (in_array($norm, ['miktar', 'stokmiktari', 'mevcutmiktar', 'adet'], true)) $map['quantity'] = $cellVal;
                if (in_array($norm, ['kritikstokesigi', 'kritikstok', 'kritikesik', 'asgaristok'], true)) $map['min_threshold'] = $cellVal;
                if (in_array($norm, ['depo', 'depoadi', 'bulundugudepo'], true)) $map['warehouse'] = $cellVal;
                if (in_array($norm, ['raflokasyon', 'raf', 'rafkodu', 'lokasyon'], true)) $map['rack_location'] = $cellVal;
                if (in_array($norm, ['birimfiyat', 'birimfiyattl', 'fiyat', 'tutar'], true)) $map['unit_price'] = $cellVal;
            }
            $rowMaps[] = $map;
        }

        return [
            'headers' => $headers,
            'rows'    => $rowMaps,
            'total'   => count($rowMaps)
        ];
    }

    /**
     * Generate professional Excel (.xlsx) or CSV template for download
     */
    public static function generateSampleTemplate(string $type, string $format = 'xlsx'): void
    {
        $typeMap = [
            'inventory' => 'Envanter',
            'personnel' => 'Personel',
            'stock'     => 'Stok'
        ];
        $typeName = $typeMap[$type] ?? 'Envanter';

        if ($type === 'inventory') {
            $headers = ['Demirbaş No', 'Cihaz Adı', 'Kategori', 'Marka', 'Model', 'Seri No', 'Durum', 'Müdürlük', 'Alım Tarihi'];
        } elseif ($type === 'personnel') {
            $headers = ['Sicil No', 'Ad', 'Soyad', 'TC No', 'Unvan', 'Müdürlük', 'Birim', 'E-posta', 'Telefon', 'Oda No'];
        } else { // stock
            $headers = ['Stok Kodu', 'Malzeme Adı', 'Mevcut Miktar', 'Birim', 'Kritik Stok Eşiği', 'Birim Fiyat (TL)'];
        }

        if ($format === 'csv') {
            $filename = "Belediye_{$typeName}_Sablonu.csv";
            if (!headers_sent()) {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }
            echo "sep=;\r\n";
            echo "\xEF\xBB\xBF";
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers, ';');
            fclose($output);
            exit;
        }

        // Default: Professional OpenXML .xlsx Template using XlsxWriter
        $filename = "Belediye_{$typeName}_Sablonu.xlsx";
        $writer = new XlsxWriter("{$typeName} Şablonu");
        $writer->setHeaders($headers);

        // Add 30 empty formatted grid rows for user data entry
        for ($i = 0; $i < 30; $i++) {
            $writer->addRow(array_fill(0, count($headers), ''));
        }

        $xlsxBinary = $writer->build();

        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($xlsxBinary));
            header('Cache-Control: max-age=0');
        }

        echo $xlsxBinary;
        exit;
    }
}
