<?php

namespace App\Exports;

use App\Helpers\Helper;
use App\Services\ReportRevenueStoreRankService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueKeyQcExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    public function __construct(
        protected array                         $headers,
        protected                               $query,
        protected                               $searchParams,
        protected                               $rowColumns,
        protected ReportRevenueStoreRankService $service,
    )
    {

    }

    public function query()
    {
        return $this->query;
    }

    public function prepareRows($results)
    {
        return $this->service->parseResultExport($results, $this->searchParams);
    }

    public function headings(): array
    {
        return collect($this->headers)
            ->map(function ($items) {
                return array_map(function ($item) {
                    return is_array($item) ? ($item['value'] ?? '') : $item;
                }, $items);
            })
            ->toArray();
    }

    public function map($row): array
    {
        $rows = [];
        foreach ($this->rowColumns as $_key) {
            if ($_key) {
                $rows[] = $row->{$_key} ?? '';
            } else {
                $rows[] = '';
            }
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        foreach ($this->headers as $i => $header) {
            foreach ($header as $j => $item) {
                if (is_array($item)) {
                    $letter = Helper::getExcelNameFromNumber($j);
                    if (isset($item['colspan']) && isset($item['rowspan'])) {
                        $lastLetter = Helper::getExcelNameFromNumber($j + $item['colspan']);
                        $sheet->mergeCells("$letter$i:$lastLetter" . ($i + $item['rowspan']));
                    } elseif (isset($item['colspan'])) {
                        $lastLetter = Helper::getExcelNameFromNumber($j + $item['colspan']);
                        $sheet->mergeCells("$letter$i:$lastLetter$i");
                    } elseif (isset($item['rowspan'])) {
                        $sheet->mergeCells("$letter$i:$letter{$item['rowspan']}");
                    }
                }
            }
        }
        $lastLetter = Helper::getExcelNameFromNumber(count($this->headers[1]) - 1);
        $sheet->getStyle("A1:{$lastLetter}3")->getAlignment()->applyFromArray([
            'horizontal' => 'center', 'vertical' => 'center'
        ]);
        $sheet->getStyle("A1:{$lastLetter}3")->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => 'thin']
        ]);
    }

    public function columnFormats(): array
    {
        $columns = [];
        foreach ($this->headers as $i => $header) {
            foreach ($header as $j => $item) {
                if (is_array($item) && isset($item['format'])) {
                    $letter           = Helper::getExcelNameFromNumber($j);
                    $columns[$letter] = $item['format'];
                }
            }
        }

        return $columns;
    }
}
