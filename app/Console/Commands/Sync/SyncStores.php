<?php

namespace App\Console\Commands\Sync;

use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms:sync_stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $maps = [
        'id'              => 'id',
        'name'            => 'name',
        'code'            => 'code',
        'organization_id' => '',
        'province_id'     => 'local_province_id',
        'district_id'     => 'local_district_id',
        'ward_id'         => 'local_town_id',
        'address'         => 'address',
        'lng'             => 'lng',
        'lat'             => 'lat',
        'phone_owner'     => 'phone',
        'phone_web'       => '',
        'parent_id'       => '',
        'vat_parent'      => '',
        'vat_buyer'       => 'vat_buyer_name',
        'vat_company'     => 'vat_company_name',
        'vat_address'     => 'vat_company_address',
        'vat_number'      => 'vat_number',
        'vat_email'       => 'email',
        'note_private'    => '',
        'status'          => 'active',
        'created_by'      => '',
        'created_at'      => 'timestamp_x',
        'updated_at'      => 'updated_at',
    ];

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
        $v_stores = DB::connection('tm01')->table('v_crm_store')->get();
        try {
            foreach ($v_stores as $v_store) {
                $store = $this->map_fields($v_store);
                $store->save();
                echo '*';
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage(), $v_store, $store);
        }

        $this->newLine();
        return 0;
    }

    function map_fields($v_store): Store
    {
        $store = Store::query()->find($v_store->id);
        if (!$store) {
            $store = new Store();
        }
        foreach ($this->maps as $n_key => $o_key) {
            if ($o_key) {
                $_value = $v_store->{$o_key};
                if ($o_key == 'active') {
                    $_value = $_value == 5 ? 1 : 0;
                } elseif ($n_key == 'phone_owner') {
                    $_value = $_value ?: '';
                }
                $store->{$n_key} = $_value;
            } elseif ($n_key == 'organization_id') {
                $store->{$n_key} = 0;
            } else {
                $store->{$n_key} = null;
            }
        }
        return $store;
    }

}
