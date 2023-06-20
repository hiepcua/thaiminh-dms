<?php

namespace Database\Seeders;

use App\Models\PromotionCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Promotion extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('promotions')->truncate();
        DB::table('promotion_conditions')->truncate();
        DB::table('organization_promotions')->truncate();

        $data = json_decode(file_get_contents(__DIR__ . '/promo.json'), true);
//        dd($data);
        foreach ($data as $item) {

            $promotion = new \App\Models\Promotion();
            $promotion->fill($item['promotion']);
            $promotion->save();
            $promotion->organizations()->sync([7, 12]);

            foreach ($item['conditions'] as $condition) {
                $promo_condition               = new PromotionCondition();
                $promo_condition->promotion_id = $promotion->id;
                $promo_condition->name         = $condition['name'];
                $promo_condition->type         = $condition['type'];
                $promo_condition->condition    = json_encode($condition['condition']);
                $promo_condition->save();
            }
        }
    }
}
