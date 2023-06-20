<?php

namespace App\Helpers;

class SearchFormHelper
{
    public static function getForm(
        $route,
        $method,
        $config,
        $class = '',
        $attributes = '',
        $useExport = false,
        $routeExport = '',
        $permissionExport = '',
        $totalRecordExport = 0
    )
    {
        return view('snippets.search-form', compact(
            'route',
            'method',
            'config',
            'class',
            'attributes',
            'useExport',
            'routeExport',
            'permissionExport',
            'totalRecordExport',
        ));
    }

    //$option = [$key => $value]
    public static function getInput(
        $type,
        $name,
        $placeholder = null,
        $defaultValue = null,
        $class = null,
        $id = null,
        $options = [],
        $attributes = '',
        $divisionPickerConfig = [],
        $other_options = [],
        $yearMonthWeekConfig = [],
    )
    {
        return view('snippets.input', compact(
            'id',
            'name',
            'type',
            'placeholder',
            'defaultValue',
            'class',
            'options',
            'attributes',
            'divisionPickerConfig',
            'other_options',
            'yearMonthWeekConfig',
        ));
    }
}
