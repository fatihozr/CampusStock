<?php

namespace App\Services;

/**
 * Pure PHP XLSX Generator Service
 * Creates 100% valid native Microsoft Excel (.xlsx) files with zero external dependencies.
 * Supports UTF-8 Turkish characters, auto column widths, styling, freeze panes, autofilter, and number formatting.
 */
class XlsxWriter
{
    private array $headers = [];
    private array $rows = [];
    private array $columnWidths = [];
    private string $sheetName = 'Rapor';

    public function __construct(string $sheetName = 'Rapor')
    {
        $this->sheetName = $sheetName;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
        foreach ($headers as $colIdx => $header) {
            $len = mb_strwidth((string)$header, 'UTF-8');
            $this->columnWidths[$colIdx] = max($this->columnWidths[$colIdx] ?? 18, $len + 8);
        }
    }

    public function addRow(array $row): void
    {
        $this->rows[] = $row;
        foreach ($row as $colIdx => $val) {
            $len = mb_strwidth((string)$val, 'UTF-8');
            $this->columnWidths[$colIdx] = max($this->columnWidths[$colIdx] ?? 18, $len + 8);
        }
    }

    public function addRows(array $rows): void
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    public function build(): string
    {
        $sharedStrings = [];
        $stringMap = [];

        $getStringId = function (string $str) use (&$sharedStrings, &$stringMap) {
            if (isset($stringMap[$str])) {
                return $stringMap[$str];
            }
            $id = count($sharedStrings);
            $sharedStrings[] = $str;
            $stringMap[$str] = $id;
            return $id;
        };

        // 1. Build Shared Strings XML
        // Pass 1: Register all headers and string values
        foreach ($this->headers as $h) {
            $getStringId((string)$h);
        }
        
        foreach ($this->rows as $r) {
            foreach ($r as $val) {
                if ($val !== null && !is_int($val) && !is_float($val)) {
                    $getStringId((string)$val);
                }
            }
        }

        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach ($sharedStrings as $str) {
            $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars((string)$str, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';

        // 2. Build Sheet XML
        $colCount = max(count($this->headers), !empty($this->rows) ? max(array_map('count', $this->rows)) : 0);
        $rowCount = count($this->rows) + 1;
        $maxColLetter = $this->getColLetter($colCount - 1);

        $shXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $shXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        
        // Sheet views (Freeze header row)
        $shXml .= '<sheetViews><sheetView tabSelected="1" workbookViewId="0">';
        $shXml .= '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>';
        $shXml .= '</sheetView></sheetViews>';

        // Column widths
        $shXml .= '<cols>';
        for ($i = 0; $i < $colCount; $i++) {
            $w = min(max($this->columnWidths[$i] ?? 18, 18), 65);
            $shXml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
        }
        $shXml .= '</cols>';

        // Sheet Data
        $shXml .= '<sheetData>';

        // Header Row (Row 1)
        $shXml .= '<row r="1" ht="24" customHeight="1">';
        foreach ($this->headers as $colIdx => $headerText) {
            $cellRef = $this->getColLetter($colIdx) . '1';
            $sId = $getStringId((string)$headerText);
            $shXml .= '<c r="' . $cellRef . '" t="s" s="1"><v>' . $sId . '</v></c>';
        }
        $shXml .= '</row>';

        // Data Rows (Row 2 .. N)
        foreach ($this->rows as $rIdx => $row) {
            $rowNum = $rIdx + 2;
            $shXml .= '<row r="' . $rowNum . '" ht="20" customHeight="1">';
            foreach ($row as $cIdx => $val) {
                $cellRef = $this->getColLetter($cIdx) . $rowNum;
                if ($val === null || $val === '') {
                    $shXml .= '<c r="' . $cellRef . '" s="2"/>';
                } elseif (is_float($val)) {
                    // Currency / Decimal float style 3
                    $shXml .= '<c r="' . $cellRef . '" s="3"><v>' . $val . '</v></c>';
                } elseif (is_int($val)) {
                    // Integer number style 2
                    $shXml .= '<c r="' . $cellRef . '" s="2"><v>' . $val . '</v></c>';
                } else {
                    // Shared String
                    $sId = $getStringId((string)$val);
                    $shXml .= '<c r="' . $cellRef . '" t="s" s="2"><v>' . $sId . '</v></c>';
                }
            }
            $shXml .= '</row>';
        }
        $shXml .= '</sheetData>';

        // AutoFilter
        if ($colCount > 0 && $rowCount > 0) {
            $shXml .= '<autoFilter ref="A1:' . $maxColLetter . $rowCount . '"/>';
        }

        $shXml .= '</worksheet>';

        // 3. Build Styles XML
        $stXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $stXml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $stXml .= '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00 &quot;TL&quot;"/></numFmts>';
        $stXml .= '<fonts count="2">';
        $stXml .= '<font><sz val="10"/><color theme="1"/><name val="Segoe UI"/></font>'; // Style 0: Normal
        $stXml .= '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Segoe UI"/></font>'; // Style 1: Header bold white
        $stXml .= '</fonts>';
        $stXml .= '<fills count="3">';
        $stXml .= '<fill><patternFill patternType="none"/></fill>';
        $stXml .= '<fill><patternFill patternType="gray125"/></fill>';
        $stXml .= '<fill><patternFill patternType="solid"><fgColor rgb="FF0F172A"/><bgColor indexed="64"/></patternFill></fill>'; // Fill 2: Dark Slate Navy #0F172A
        $stXml .= '</fills>';
        $stXml .= '<borders count="2">';
        $stXml .= '<border><left/><right/><top/><bottom/><diagonal/></border>';
        $stXml .= '<border><left style="thin"><color rgb="E2E8F0"/></left><right style="thin"><color rgb="E2E8F0"/></right><top style="thin"><color rgb="E2E8F0"/></top><bottom style="thin"><color rgb="E2E8F0"/></bottom></border>';
        $stXml .= '</borders>';
        $stXml .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $stXml .= '<cellXfs count="4">';
        $stXml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'; // 0: default
        $stXml .= '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'; // 1: Header
        $stXml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'; // 2: Data Normal
        $stXml .= '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'; // 3: Currency
        $stXml .= '</cellXfs>';
        $stXml .= '</styleSheet>';

        // 4. Build Workbook XML
        $wbXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $wbXml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $wbXml .= '<sheets><sheet name="' . htmlspecialchars($this->sheetName, ENT_QUOTES, 'UTF-8') . '" sheetId="1" r:id="rId1"/></sheets>';
        $wbXml .= '</workbook>';

        // 5. Build Content Types XML
        $ctXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $ctXml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $ctXml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $ctXml .= '<Default Extension="xml" ContentType="application/xml"/>';
        $ctXml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $ctXml .= '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $ctXml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $ctXml .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
        $ctXml .= '</Types>';

        // 6. Build Rels XMLs
        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $relsXml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $relsXml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $relsXml .= '</Relationships>';

        $wbRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $wbRelsXml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $wbRelsXml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>';
        $wbRelsXml .= '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $wbRelsXml .= '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
        $wbRelsXml .= '</Relationships>';

        // Package all files into a Zip buffer using custom Zip builder
        $zip = new ZipBuilder();
        $zip->addFile('[Content_Types].xml', $ctXml);
        $zip->addFile('_rels/.rels', $relsXml);
        $zip->addFile('xl/workbook.xml', $wbXml);
        $zip->addFile('xl/_rels/workbook.xml.rels', $wbRelsXml);
        $zip->addFile('xl/styles.xml', $stXml);
        $zip->addFile('xl/sharedStrings.xml', $ssXml);
        $zip->addFile('xl/worksheets/sheet1.xml', $shXml);

        return $zip->output();
    }

    private function getColLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex >= 0) {
            $letter = chr(($colIndex % 26) + 65) . $letter;
            $colIndex = (int)($colIndex / 26) - 1;
        }
        return $letter;
    }
}

/**
 * Pure PHP Zip Archive Builder using gzdeflate
 */
class ZipBuilder
{
    private string $datasec = '';
    private array $ctrlDir = [];
    private int $offset = 0;

    public function addFile(string $name, string $data): void
    {
        $name = str_replace('\\', '/', $name);
        $uncDataLen = strlen($data);
        $crc = crc32($data);
        $zdata = gzdeflate($data);
        $cDataLen = strlen($zdata);

        // Local Header
        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00"; // Version needed to extract
        $fr .= "\x00\x00"; // General purpose bit flag
        $fr .= "\x08\x00"; // Compression method (deflated)
        $fr .= "\x00\x00\x00\x00"; // Last mod time/date
        $fr .= pack('V', $crc);
        $fr .= pack('V', $cDataLen);
        $fr .= pack('V', $uncDataLen);
        $fr .= pack('v', strlen($name));
        $fr .= pack('v', 0);
        $fr .= $name;
        $fr .= $zdata;

        $this->datasec .= $fr;

        // Central Directory Entry
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00"; // Version made by
        $cdrec .= "\x14\x00"; // Version needed
        $cdrec .= "\x00\x00"; // General purpose bit flag
        $cdrec .= "\x08\x00"; // Compression method
        $cdrec .= "\x00\x00\x00\x00"; // Last mod time/date
        $cdrec .= pack('V', $crc);
        $cdrec .= pack('V', $cDataLen);
        $cdrec .= pack('V', $uncDataLen);
        $cdrec .= pack('v', strlen($name));
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('V', 32); // Internal file attributes
        $cdrec .= pack('V', $this->offset);
        $cdrec .= $name;

        $this->ctrlDir[] = $cdrec;
        $this->offset += strlen($fr);
    }

    public function output(): string
    {
        $ctrldir = implode('', $this->ctrlDir);
        return $this->datasec . $ctrldir .
            "\x50\x4b\x05\x06\x00\x00\x00\x00" .
            pack('v', count($this->ctrlDir)) .
            pack('v', count($this->ctrlDir)) .
            pack('V', strlen($ctrldir)) .
            pack('V', strlen($this->datasec)) .
            "\x00\x00";
    }
}
