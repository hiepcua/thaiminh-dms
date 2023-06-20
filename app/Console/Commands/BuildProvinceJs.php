<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BuildProvinceJs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:build_province_js';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 1;
        $province_values = [
            'provinces' => Province::query()->select('id', 'province_name_with_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'            => $item->id,
                        'province_name' => $item->province_name_with_type,
                    ];
                }),
            'districts' => District::query()->select('id', 'province_id', 'district_name_with_type')
                ->orderByDesc('district_type')
                ->orderBy('district_name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'            => $item->id,
                        'province_id'   => $item->province_id,
                        'district_name' => $item->district_name_with_type,
                    ];
                }),
            'wards'     => Ward::query()->select('id', 'district_id', 'ward_name_with_type')
                ->orderBy('ward_type')
                ->orderBy('ward_name')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'district_id' => $item->district_id,
                        'ward_name'   => $item->ward_name_with_type,
                    ];
                }),
        ];
        foreach (['provinces', 'districts', 'wards'] as $key) {
            $filename = public_path('js/core/' . $key . '.js');
            $script   = [];
            $script[] = 'window["province_options"]["' . $key . '"] = ' . json_encode($province_values[$key]) . ';';
            File::put($filename, implode('', $script));
        }

        return 1;
    }
}
