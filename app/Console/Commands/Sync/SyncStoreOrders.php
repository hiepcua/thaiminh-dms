<?php

namespace App\Console\Commands\Sync;

use App\Models\Store;
use App\Models\StoreOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStoreOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms:sync_store_orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $map_order_fields = [
        'id'              => 'id',
        'user_id'         => 'create_by',
        'organization_id' => '',
        'agency_id'       => 'agency_id',
        'code'            => 'code',
        'booking_at'      => 'date_book',
        'delivery_at'     => 'date_delivery',
        'discount'        => 'total_discount',
        'sub_total'       => 'subtotal',
        'total_amount'    => 'total',
        'store_id'        => 'store_id',
        'paid'            => 'paid',
        'note'            => 'note',
        'status'          => 'status',//0 = chua giao, 1 = da giao
        'created_by'      => 'create_by',
        'created_at'      => 'created_at',
        'updated_at'      => 'updated_at',
    ];

    protected $store_fields = [
        'code',
        'organization_id',
        'province_id',
        'district_id',
        'ward_id',
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
        $order_ids = DB::connection('tm01')->table('v_sale_order')->select('id')->get();
        try {
            $bar = $this->output->createProgressBar($order_ids->count());
            foreach ($order_ids as $item) {
                $this->create_order($item->id);
                $bar->advance();
            }
            $bar->finish();
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

        $this->newLine();
        return 0;
    }

    function create_order($order_id)
    {
        $v_order = DB::connection('tm01')->table('v_sale_order')->where('id', $order_id)->first();
        $order   = $this->map_fields($v_order, $this->map_order_fields);
        if ($order->store_id) {
            $order = $this->map_store($order);
        }
        $order->save();
    }

    function map_store($order)
    {
        $store = Store::query()->find($order->store_id);
        if ($store) {
            foreach ($this->store_fields as $key) {
                $order->{'store_' . $key} = $store->{$key};
            }
        }
        return $order;
    }

    function map_fields($item, $fields): StoreOrder
    {
        $order = StoreOrder::query()->find($item->id);
        if (!$order) {
            $order = new StoreOrder();
        }
        foreach ($fields as $n_key => $o_key) {
            if ($o_key) {
                $_value = $item->{$o_key};
                if ($o_key == 'active') {
                    $_value = $_value == 5 ? 1 : 0;
                } elseif ($o_key == 'paid') {
                    $_value = $_value == 'N' ? 0 : 1;
                }
                $order->{$n_key} = $_value;
            } elseif ($n_key == 'organization_id') {
                $order->{$n_key} = 0;
            } else {
                $order->{$n_key} = null;
            }
        }
        return $order;
    }
}
