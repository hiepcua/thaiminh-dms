<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TDVRevenueDetailExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithColumnFormatting
{
    protected $headers;
    protected $rows;
    protected $totalRow = 0;

    public function __construct($headers, $rows)
    {
        $this->headers  = $headers;
        $this->rows     = $rows;
        $this->totalRow = count($rows);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function headings(): array
    {
        return collect($this->headers)->map(function ($values) {
            return array_map(function ($item) {
                return $item['value'] ?? '';
            }, $values);
        })->toArray();
    }

    public function styles(Worksheet $sheet)
    {

    }
}
