<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AsmRevenueDetailExport implements FromView, ShouldAutoSize
{
    protected array $headers;
    protected Collection $rows;
    protected array $rowColumns;
    protected int $totalRow = 0;
    protected array $asmIndex = [];

    public function __construct(
        protected $arrTdv,
        protected $data
    )
    {}

    public function view(): View
    {
        return view('exports.detailAsmRevenue', [
            'arrTdv' => $this->arrTdv,
            'data' => $this->data
        ]);
    }
}
