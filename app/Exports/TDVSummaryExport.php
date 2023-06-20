<?php

namespace App\Exports;

use App\Helpers\Helper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TDVSummaryExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithColumnFormatting
{
    protected array $headers;
    protected Collection $rows;
    protected array $rowColumns;
    protected int $totalRow = 0;
    protected array $asmIndex = [];

    public function __construct(protected array $summaryRevenue)
    {
        $this->headers    = $this->summaryRevenue['headers'];
        $this->rows       = collect($summaryRevenue['asmRows'])->flatten(1);
        $this->totalRow   = $this->rows->count();
        $this->rowColumns = $this->summaryRevenue['rowColumns'];

        $this->asmIndex = $this->rows->filter(function ($item) {
            $stt = $item['col_stt']['value'] ?? '';
            return $stt == 'ASM';
        })->keys()->toArray();
    }

    public function array(): array
    {
        return $this->rows->map(function ($values) {
            $row = [];
            foreach ($this->rowColumns as $col) {
                $row[] = $values[$col]['value'] ?? $values[$col] ?? '';
            }
            return $row;
        })->toArray();
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
        foreach ($this->headers as $rowIndex => $header) {
            foreach ($header as $colIndex => $value) {
                $rowspan = $value['attributes']['rowspan'] ?? 0;
                $colspan = $value['attributes']['colspan'] ?? 0;
                $colName = Helper::getExcelNameFromNumber($colIndex);

                $rowspanToIndex = $rowspan ? ($rowIndex + $rowspan - 1) : 0;
                $colspanToIndex = $colspan ? ($colIndex + $colspan - 1) : 0;

                $checkRowspan = $rowspanToIndex && $rowspanToIndex != $rowIndex;
                $checkColspan = $colspanToIndex && $colspanToIndex != $colIndex;

                if ($checkRowspan && $checkColspan) {
                    $toColName = Helper::getExcelNameFromNumber($colspanToIndex);
                    $sheet->mergeCells("$colName$rowIndex:$toColName$rowspanToIndex");
                } elseif ($checkRowspan) {
                    $sheet->mergeCells("$colName$rowIndex:$colName$rowspanToIndex");
                } elseif ($checkColspan) {
                    $toColName = Helper::getExcelNameFromNumber($colspanToIndex);
                    $sheet->mergeCells("$colName$rowIndex:$toColName$rowIndex");
                }
            }
        }

        $totalHeaderRow = count($this->headers);
        $lastCol        = Helper::getExcelNameFromNumber(count($this->rowColumns) - 1);
        $headerStyle    = $sheet->getStyle(sprintf('A1:%s%s', $lastCol, $totalHeaderRow));

        $headerStyle->getAlignment()
            ->applyFromArray([
                'horizontal' => 'center', 'vertical' => 'center'
            ]);
        $headerStyle->getFill()->applyFromArray([
            'fillType' => Fill::FILL_SOLID,
            'color'    => ['argb' => Color::COLOR_YELLOW]
        ]);
        $sheet->getStyle(sprintf('A1:%s%s', $lastCol, $this->totalRow + $totalHeaderRow))
            ->getBorders()
            ->applyFromArray([
                'allBorders' => ['borderStyle' => 'thin']
            ]);
        foreach ($this->asmIndex as $asmIndex) {
            $row = sprintf('A%s:%s%s', $asmIndex + 1 + $totalHeaderRow, $lastCol, $asmIndex + 1 + $totalHeaderRow);
            $sheet->getStyle($row)->getFill()->applyFromArray([
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['argb' => Color::COLOR_YELLOW]
            ]);
        }
    }

    public function columnFormats(): array
    {
        $columns = [];
        for ($i = 4; $i <= count($this->rowColumns); $i++) {
            $columns[Helper::getExcelNameFromNumber($i)] = '#,##0';
        }

        return $columns;
    }
}
