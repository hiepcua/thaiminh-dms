<?php

namespace App\Exports;

use App\Models\PosterStoreRegister;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PosterRegisterExport implements FromView, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $headers;
    protected $rows;

    public function __construct($headers, $rows)
    {
        $this->headers = $headers;
        $this->rows    = $rows;
    }

    public function view(): View
    {
        $headers = $this->headers;
        $datas = $this->rows;
        return view('pages.posters-agency.table', compact('datas', 'headers'));
        // TODO: Implement view() method.
    }
    public function styles(Worksheet $sheet)
    {
        return [
            '1' => ['font' => ['bold' => true]],
            '2' => ['font' => ['bold' => true]],
            '3' => ['font' => ['bold' => true]],

//            'C'  => ['font' => ['size' => 16]],
        ];
    }
}
