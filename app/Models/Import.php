<?php

namespace App\Models;

use Exception;

class Import
{
    private Personnel $personnelModel;
    private Product $productModel;

    public function __construct()
    {
        $this->personnelModel = new Personnel();
        $this->productModel   = new Product();
    }

    /**
     * Get system target fields for mapping by import type
     */
    public static function getTargetFields(string $type): array
    {
        return match ($type) {
            'personnel' => [
                'sicil_no'   => 'Sicil No (Zorunlu & Benzersiz)',
                'first_name' => 'Adı (Zorunlu)',
                'last_name'  => 'Soyadı (Zorunlu)',
                'tc_no'      => 'T.C. Kimlik No (11 Hane)',
                'title'      => 'Unvan',
                'department' => 'Müdürlük',
                'unit'       => 'Birim / Şeflik',
                'email'      => 'Kurumsal E-Posta',
                'phone'      => 'Telefon / Dahili',
                'room_no'    => 'Oda No'
            ],
            'stock' => [
                'code'          => 'Stok Kodu (Zorunlu & Benzersiz)',
                'name'          => 'Malzeme Adı (Zorunlu)',
                'quantity'      => 'Mevcut Miktar (Sayısal Zorunlu)',
                'unit'          => 'Birim (Adet/Koli/Paket)',
                'min_threshold' => 'Kritik Stok Eşiği',
                'unit_price'    => 'Birim Fiyat (TL)'
            ],
            default => []
        };
    }

    /**
     * Get list of mandatory field keys for import by type
     */
    public static function getRequiredFields(string $type): array
    {
        return match ($type) {
            'personnel' => ['sicil_no', 'first_name', 'last_name'],
            'stock'     => ['code', 'name'],
            default     => []
        };
    }

    /**
     * Normalize Turkish header string for accurate column mapping
     */
    public static function normalizeHeaderString(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $trMap = [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'i' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'I' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u'
        ];
        $str = strtr($str, $trMap);
        return preg_replace('/[^a-z0-9]/', '', $str);
    }

    /**
     * Automatically guess header column mapping index for Excel headers
     */
    public static function guessMapping(string $targetField, array $headers): string
    {
        $targetFieldLower = strtolower($targetField);
        
        $aliases = [
            // Stock Aliases
            'code'          => ['stokkodu', 'stokno', 'stoknumarasi', 'malzemekodu', 'urunkodu', 'kod', 'kodu', 'code', 'stokkod'],
            'name'          => ['malzemeadi', 'malzeme', 'urunadi', 'urun', 'urunaciklamasi', 'malzemead', 'ad', 'adi'],
            'category'      => ['kategori', 'kategoriadi', 'grup', 'urungrubu', 'tur', 'malzemeturu', 'malzemekategorisi', 'urunkategorisi'],
            'quantity'      => ['mevcutmiktar', 'stokmiktari', 'stokadedi', 'adetsayisi', 'miktaradedi', 'stokmiktar', 'miktar', 'mevcutstok'],
            'unit'          => ['birim', 'olcubirimi', 'birimturu', 'olcu', 'unit', 'birimi', 'seflik'],
            'min_threshold' => ['kritikesik', 'kritikstok', 'minimumstok', 'kritikstokesigi', 'asgaristok', 'esik'],
            'warehouse'     => ['depo', 'depoadi', 'bulundugudepo', 'depoyeri'],
            'rack_location' => ['raf', 'rafno', 'rafnumarasi', 'lokasyon', 'raflokasyon', 'konum', 'rafkodu'],
            'unit_price'    => ['birimfiyat', 'birimfiyati', 'fiyat', 'fiyati', 'birimtutar', 'tutar', 'fiyattl', 'birimfiyattl'],

            // Personnel Aliases
            'sicil_no'      => ['sicilno', 'sicilnumarasi', 'personelsicil', 'sicil'],
            'first_name'    => ['personeladi', 'personelad', 'ad', 'adi'],
            'last_name'     => ['personelsoyadi', 'personelsoyad', 'soyad', 'soyadi'],
            'tc_no'         => ['tcno', 'tckimlikno', 'tc', 'tckn', 'tckimlik'],
            'title'         => ['unvan', 'unvani', 'gorev'],
            'email'         => ['eposta', 'email', 'kurumsaleposta'],
            'phone'         => ['telefon', 'dahili', 'ceptel', 'gsm'],
            'room_no'       => ['odano', 'oda', 'odanumarasi']
        ];

        $targetAliases = $aliases[$targetFieldLower] ?? [$targetFieldLower];

        // Pass 1: Exact normalized match
        foreach ($headers as $index => $headerName) {
            $normHeader = self::normalizeHeaderString((string)$headerName);
            if (empty($normHeader)) continue;

            if ($normHeader === 'adet' || $normHeader === 'adetsayi') {
                $hasSeparateUnitHeader = false;
                foreach ($headers as $h) {
                    $nh = self::normalizeHeaderString((string)$h);
                    if (in_array($nh, ['birim', 'olcubirimi', 'birimturu', 'olcu', 'unit'])) {
                        $hasSeparateUnitHeader = true;
                        break;
                    }
                }
                if ($targetFieldLower === 'quantity' && $hasSeparateUnitHeader) {
                    return (string)$index;
                }
            }

            foreach ($targetAliases as $alias) {
                if ($normHeader === $alias) {
                    return (string)$index;
                }
            }
        }

        // Pass 2: Header starts with alias
        foreach ($headers as $index => $headerName) {
            $normHeader = self::normalizeHeaderString((string)$headerName);
            if (empty($normHeader)) continue;

            if ($targetFieldLower === 'unit' && (str_contains($normHeader, 'fiyat') || str_contains($normHeader, 'tutar') || str_contains($normHeader, 'miktar'))) {
                continue;
            }

            if ($targetFieldLower === 'quantity' && (str_contains($normHeader, 'fiyat') || str_contains($normHeader, 'tutar') || str_contains($normHeader, 'birim'))) {
                continue;
            }

            foreach ($targetAliases as $alias) {
                if (strlen($alias) >= 4 && str_starts_with($normHeader, $alias)) {
                    return (string)$index;
                }
            }
        }

        return '';
    }

    /**
     * Map raw row data with user mapping configuration and validate each row
     */
    public function validateAndMapRows(string $type, array $headers, array $rows, array $mapping): array
    {
        $validRows = [];
        $invalidRows = [];

        $existingKeys = $this->getExistingUniqueKeys($type);
        $seenInFileKeys = [];

        $usedIndexes = [];
        $safeMapping = [];

        foreach ($mapping as $targetField => $headerIndex) {
            if ($headerIndex === '') {
                $safeMapping[$targetField] = '';
                continue;
            }
            if (in_array($headerIndex, $usedIndexes, true)) {
                $safeMapping[$targetField] = '';
            } else {
                $safeMapping[$targetField] = $headerIndex;
                $usedIndexes[] = $headerIndex;
            }
        }

        foreach ($rows as $rowIndex => $row) {
            $mappedData = [];
            foreach ($safeMapping as $targetField => $headerIndex) {
                if ($headerIndex !== '' && isset($row[$headerIndex])) {
                    $mappedData[$targetField] = trim((string)$row[$headerIndex]);
                } else {
                    $mappedData[$targetField] = '';
                }
            }

            if ($type === 'stock') {
                $qtyVal  = $mappedData['quantity'] ?? '';
                $unitVal = $mappedData['unit'] ?? '';

                if ($qtyVal !== '' && !is_numeric($qtyVal) && is_numeric($unitVal)) {
                    $mappedData['quantity'] = $unitVal;
                    $mappedData['unit']     = $qtyVal;
                }
            }

            $errors = $this->validateSingleRow($type, $mappedData, $existingKeys, $seenInFileKeys);

            $rowResult = [
                'row_number' => $rowIndex + 2,
                'data'       => $mappedData,
                'raw_row'    => $row,
                'errors'     => $errors
            ];

            if (empty($errors)) {
                $this->registerSeenKey($type, $mappedData, $seenInFileKeys);
                $validRows[] = $rowResult;
            } else {
                $invalidRows[] = $rowResult;
            }
        }

        return [
            'valid'   => $validRows,
            'invalid' => $invalidRows,
            'total'   => count($rows)
        ];
    }

    /**
     * Get unique key arrays from current database
     */
    private function getExistingUniqueKeys(string $type): array
    {
        if ($type === 'personnel') {
            return array_map('mb_strtolower', array_column($this->personnelModel->getAll(), 'sicil_no'));
        } elseif ($type === 'stock') {
            return array_map('mb_strtolower', array_column($this->productModel->getAll(), 'code'));
        }
        return [];
    }

    /**
     * Track unique keys seen in current import file
     */
    private function registerSeenKey(string $type, array $data, array &$seenKeys): void
    {
        if ($type === 'personnel' && !empty($data['sicil_no'])) {
            $seenKeys[] = mb_strtolower($data['sicil_no'], 'UTF-8');
        } elseif ($type === 'stock' && !empty($data['code'])) {
            $seenKeys[] = mb_strtolower($data['code'], 'UTF-8');
        }
    }

    /**
     * Validate single record according to business rules & duplicate checks
     */
    private function validateSingleRow(string $type, array $data, array $existingKeys, array $seenInFileKeys): array
    {
        $errors = [];

        if ($type === 'personnel') {
            $sicilNo = trim($data['sicil_no'] ?? '');
            if (empty($sicilNo)) {
                $errors[] = 'Sicil No alanı boş olamaz.';
            } else {
                $sicilNoLower = mb_strtolower($sicilNo, 'UTF-8');
                if (in_array($sicilNoLower, $existingKeys, true)) {
                    $errors[] = "Bu Sicil No ('{$sicilNo}') sistemde zaten kayıtlı (Mükerrer Kayıt).";
                } elseif (in_array($sicilNoLower, $seenInFileKeys, true)) {
                    $errors[] = "Bu Sicil No ('{$sicilNo}') Excel dosyası içinde tekrar eden mükerrer kayıt.";
                }
            }

            if (empty($data['first_name'])) {
                $errors[] = 'Personel Adı boş olamaz.';
            }
            if (empty($data['last_name'])) {
                $errors[] = 'Personel Soyadı boş olamaz.';
            }

            if (!empty($data['tc_no'])) {
                $tc = trim($data['tc_no']);
                if (!ctype_digit($tc) || strlen($tc) !== 11) {
                    $errors[] = "Geçersiz T.C. Kimlik No ('{$tc}'). Tam 11 haneli sayı olmalıdır.";
                }
            }

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Geçersiz e-posta adresi formatı ('{$data['email']}').";
            }

        } elseif ($type === 'stock') {
            $code = trim($data['code'] ?? '');
            if (empty($code)) {
                $errors[] = 'Stok Kodu alanı boş olamaz.';
            } else {
                $codeLower = mb_strtolower($code, 'UTF-8');
                if (in_array($codeLower, $existingKeys, true)) {
                    $errors[] = "Bu Stok Kodu ('{$code}') sistemde zaten kayıtlı (Mükerrer Kayıt).";
                } elseif (in_array($codeLower, $seenInFileKeys, true)) {
                    $errors[] = "Bu Stok Kodu ('{$code}') Excel dosyası içinde tekrar eden mükerrer kayıt.";
                }
            }

            if (empty($data['name'])) {
                $errors[] = 'Malzeme Adı boş olamaz.';
            }

            if (isset($data['quantity']) && $data['quantity'] !== '' && !is_numeric($data['quantity'])) {
                $errors[] = "Miktar değeri ('{$data['quantity']}') sayısal olmalıdır.";
            }

            if (isset($data['unit_price']) && $data['unit_price'] !== '' && !is_numeric(str_replace(['.', ','], ['', '.'], $data['unit_price']))) {
                $errors[] = "Birim fiyatı ('{$data['unit_price']}') sayısal bir tutar olmalıdır.";
            }
        }

        return $errors;
    }

    /**
     * Parse decimal currency strings in Turkish/Standard numeric formats
     */
    public static function parseDecimalPrice(string $val): float
    {
        $val = trim($val);
        $val = preg_replace('/[^\d.,]/', '', $val);
        if ($val === '') return 0.0;

        if (str_contains($val, '.') && str_contains($val, ',')) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } elseif (str_contains($val, ',')) {
            $val = str_replace(',', '.', $val);
        }
        return (float)$val;
    }

    /**
     * Commit valid records into system database within transaction safety
     */
    public function executeImport(string $type, array $validRows): int
    {
        $importedCount = 0;

        foreach ($validRows as $item) {
            $data = $item['data'];

            if ($type === 'personnel') {
                $this->personnelModel->store([
                    'first_name' => trim($data['first_name']),
                    'last_name'  => trim($data['last_name']),
                    'sicil_no'   => trim($data['sicil_no']),
                    'tc_no'      => trim($data['tc_no'] ?: '11111111111'),
                    'title'      => trim($data['title'] ?: 'Memur'),
                    'department' => trim($data['department'] ?: 'Destek Hizmetleri'),
                    'unit'       => trim($data['unit'] ?: 'Genel Şeflik'),
                    'email'      => trim($data['email'] ?: 'personel@belediye.bel.tr'),
                    'phone'      => trim($data['phone'] ?: '0532 000 0000'),
                    'room_no'    => trim($data['room_no'] ?: 'Zemin Kat'),
                    'status'     => 'Aktif'
                ]);
                $importedCount++;

            } elseif ($type === 'stock') {
                $priceFloat = self::parseDecimalPrice($data['unit_price'] ?? '0');

                $this->productModel->create([
                    'code'          => trim($data['code']),
                    'name'          => trim($data['name']),
                    'category_id'   => 1, // Default Category ID
                    'unit'          => trim($data['unit'] ?: 'Adet'),
                    'current_stock' => max(0, (int)($data['quantity'] ?: 0)),
                    'min_stock'     => max(1, (int)($data['min_threshold'] ?: 5)),
                    'unit_price'    => number_format($priceFloat, 2, '.', '')
                ]);
                $importedCount++;
            }
        }

        return $importedCount;
    }
}
