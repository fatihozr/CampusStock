<?php

namespace App\Services;

use App\Models\PrintJob;
use Exception;

class PrintJobService
{
    private PrintJob $printJobModel;

    // Standard Campus Stationery Printing Rates (TRY)
    public const PRICE_BW_SINGLE = 1.00;
    public const PRICE_BW_DOUBLE = 1.50; // Per 2 pages (1 sheet front/back)
    public const PRICE_COLOR_SINGLE = 5.00;
    public const PRICE_COLOR_DOUBLE = 8.00;
    public const PRICE_BINDING_NONE = 0.00;
    public const PRICE_BINDING_STAPLE = 5.00;
    public const PRICE_BINDING_SPIRAL = 35.00;

    public function __construct()
    {
        $this->printJobModel = new PrintJob();
    }

    public function getPricingTable(): array
    {
        return [
            'bw' => [
                'single_sided' => self::PRICE_BW_SINGLE,
                'double_sided' => self::PRICE_BW_DOUBLE
            ],
            'color' => [
                'single_sided' => self::PRICE_COLOR_SINGLE,
                'double_sided' => self::PRICE_COLOR_DOUBLE
            ],
            'binding' => [
                'none'   => self::PRICE_BINDING_NONE,
                'staple' => self::PRICE_BINDING_STAPLE,
                'spiral' => self::PRICE_BINDING_SPIRAL
            ],
            'currency' => 'TRY'
        ];
    }

    /**
     * Calculate cost for print parameters
     */
    public function calculateCost(array $data): array
    {
        $pageCount   = max(1, (int)($data['page_count'] ?? 1));
        $colorMode   = ($data['color_mode'] ?? 'bw') === 'color' ? 'color' : 'bw';
        $doubleSided = !empty($data['double_sided']);
        $binding     = $data['binding'] ?? 'none';
        $copies      = max(1, (int)($data['copies'] ?? 1));

        $sheetCount = $doubleSided ? (int)ceil($pageCount / 2) : $pageCount;

        if ($colorMode === 'color') {
            $pagePrice = $doubleSided ? (self::PRICE_COLOR_DOUBLE / 2) : self::PRICE_COLOR_SINGLE;
        } else {
            $pagePrice = $doubleSided ? (self::PRICE_BW_DOUBLE / 2) : self::PRICE_BW_SINGLE;
        }

        $printCostPerCopy = round($pageCount * $pagePrice, 2);

        $bindingCost = match($binding) {
            'staple' => self::PRICE_BINDING_STAPLE,
            'spiral' => self::PRICE_BINDING_SPIRAL,
            default  => self::PRICE_BINDING_NONE
        };

        $totalPerCopy = $printCostPerCopy + $bindingCost;
        $grandTotal = round($totalPerCopy * $copies, 2);

        return [
            'page_count'           => $pageCount,
            'sheet_count'          => $sheetCount,
            'copies'               => $copies,
            'color_mode'           => $colorMode,
            'double_sided'         => $doubleSided,
            'binding'              => $binding,
            'print_cost_per_copy'  => $printCostPerCopy,
            'binding_cost'         => $bindingCost,
            'total_per_copy'       => $totalPerCopy,
            'total_price'          => $grandTotal,
            'currency'             => 'TRY'
        ];
    }

    /**
     * Submit new print request
     */
    public function submitJob(int $studentId, array $data): array
    {
        $documentName = trim($data['document_name'] ?? '');
        if (empty($documentName)) {
            throw new Exception("Belge / doküman adı belirtilmelidir.");
        }

        $calc = $this->calculateCost($data);

        $payload = [
            'student_id'    => $studentId,
            'store_id'      => (int)($data['store_id'] ?? 1),
            'document_name' => $documentName,
            'file_path'     => $data['file_path'] ?? null,
            'page_count'    => $calc['page_count'],
            'color_mode'    => $calc['color_mode'],
            'double_sided'  => $calc['double_sided'] ? 1 : 0,
            'binding'       => $calc['binding'],
            'copies'        => $calc['copies'],
            'total_price'   => $calc['total_price'],
            'notes'         => $data['notes'] ?? null
        ];

        return $this->printJobModel->create($payload);
    }
}
