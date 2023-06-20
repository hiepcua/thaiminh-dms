<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AgencyOrderHistory extends Model
{
    protected $table = 'agency_order_history';
    protected $fillable = [
	    "agency_order_id",
	    "current_info",
	    "current_items",
	    "old_info",
	    "old_items",
	    "updated_by",
	    "created_at",
	    "updated_at",
    ];

    static function addAgencyOrderHistory($oldOrder, $currentOrder) {
        try {
            self::create([
                "agency_order_id" => $currentOrder->id,
                "current_info" =>  json_encode($currentOrder->toArray()),
                "current_items" => json_encode($currentOrder->agencyOrderItems?->toArray() ?? []),
                "old_info" => json_encode($oldOrder?->toArray() ?? []),
                "old_items" => json_encode($oldOrder?->agencyOrderItems?->toArray() ?? []),
                "updated_by" => Helper::currentUser()->id,
            ]);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);
        }
    }
}
