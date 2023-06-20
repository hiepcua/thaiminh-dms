<?php

namespace App\Helpers;


class TableHelper
{
    public function __construct(
        protected $collections,
        protected $nameTable,
        protected $headers = [],
        protected $customColumns = [],
        protected $sortColumn = [],
        protected $centreColumn = [],
        protected $classCustom = [],
        protected $footerRow = '',
        protected $headerHtml = '',
        protected $isPagination = true,
        protected $totalRow = null,
        protected $styleCss = null,
    )
    {
        $this->headers       = count($headers) ? $headers : config("table.pages.$nameTable.headers", []);
        $this->customColumns = count($customColumns) ? $customColumns : config("table.pages.$nameTable.customColumns", []);
        $this->sortColumn    = count($sortColumn) ? $sortColumn : config("table.pages.$nameTable.sortColumn", []);
        $this->centreColumn  = count($centreColumn) ? $centreColumn : config("table.pages.$nameTable.centerColumn", []);
        $this->classCustom   = count($classCustom) ? $classCustom : config("table.pages.$nameTable.classCustom", []);
        $this->styleCss      = isset($styleCss) && count($styleCss) ? $styleCss : config("table.pages.$nameTable.styleCss", []);
    }

    public function isEmpty()
    {
        return $this->collections->isEmpty();
    }

    public function getTable()
    {
        $header = $this->getHeader();

        return view('snippets.table-body', [
            'headerHtml'     => $header,
            'headers'        => $this->headers,
            'customColumns'  => $this->customColumns,
            'collections'    => $this->collections,
            'nameTable'      => $this->nameTable,
            'optionPaginate' => config('table.option_paginate'),
            'centreColumn'   => $this->centreColumn,
            'classCustom'    => $this->classCustom,
            'empty'          => $this->isEmpty(),
            'footerRow'      => $this->footerRow,
            'isPagination'   => $this->isPagination,
            'totalRow'       => $this->totalRow,
        ]);
    }

    public function getHeader()
    {
        return $this->headerHtml ?: view('snippets.table-header', [
            'headers'      => $this->headers,
            'customColumn' => $this->customColumns,
            'sortColumn'   => $this->sortColumn,
            'classCustom'  => $this->classCustom,
            'styleCss'     => $this->styleCss,
        ]);
    }
}
