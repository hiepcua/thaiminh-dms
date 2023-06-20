<?php

namespace App\Console\Commands\Organization;

use App\Models\District;
use App\Models\Organization;
use App\Models\Province;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CreateOrganization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms:create_organization';

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

//    protected $settings = [
//        'MB' => [
//            'file'   => 'tmmb.json',
//            'parent' => 3,
//        ],
//        'MN' => [
//            'file'   => 'tmmn.json',
//            'parent' => 6,
//        ],
//    ];
    protected $mien = [
        'mien_bac' => 3,
        'mien_nam' => 6,
    ];
    protected $product_groups = ['GR1' => 1, 'GR2' => 2, 'GR3' => 3];
    protected $organizations;
    protected $divisions;
    protected $provinces;
    protected $districts;
    protected $item_locality;
    protected $item_division;
    protected $item_mien;
    protected $item_giam_sat;
    protected $item_province;
    protected $item_district;
    protected $users = [];


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('organizations')->where('id', '>', 6)->delete();
        DB::table('organization_districts')->truncate();
        DB::statement('ALTER TABLE `organizations` auto_increment = 7');

        $this->provinces     = Province::all();
        $this->districts     = District::all();
        $this->organizations = Organization::all();

        $this->parseDivisionsMB();
        $this->parseDivisionsMN();
//        dd($this->divisions);
        $this->createItem();
        $this->mapUsers();


        return 0;
    }

    function mapUsers()
    {
//        dd($this->users[350]);
        foreach ($this->users as $uid => $values) {
            $user = User::query()->find($uid);
            $user->organizations()->sync($values['localities']);
            $user->product_groups()->sync(array_unique($values['groups']));
            if ($values['status'] ?? '') {
                $user->update(['status' => 0]);
            }
        }
    }

    function createItem($parent = 0)
    {
        foreach ($this->divisions as $name => $division) {
            $parent_id    = $parent ?: $division['parent'];
            $organization = Organization::query()->where('name', $name)
                ->where('type', $division['type'])
                ->first();
            if (!$organization) {
                $organization = new Organization();
            }
            $organization->fill([
                'name'      => $name,
                'parent_id' => $parent_id,
                'type'      => $division['type'],
                'status'    => 1,
            ]);
            $organization->save();
            if ($division['asm']) {
                $asm_users = User::query()->whereIn('username', array_keys($division['asm']))->get();
                foreach ($asm_users as $asm_user) {
                    $asm_user->organizations()->sync([$organization->id]);
                }
            }

            if ($division['children']) {
                foreach ($division['children'] as $item) {
                    $locality = Organization::query()->where('name', $item['name'])
                        ->where('type', $item['type'])
                        ->first();
                    if (!$locality) {
                        $locality = new Organization();
                    }
                    $locality->fill([
                        'name'        => $item['name'],
                        'parent_id'   => $organization->id,
                        'type'        => $item['type'],
                        'status'      => 1,
                        'province_id' => $item['province'] ?: null,
                    ]);
                    $locality->save();
                    $locality->districts()->sync($item['districts']);
                    if ($item['users']) {
                        $users = User::query()->select('id', 'username')->whereIn('username', array_keys($item['users']))->get();
                        if (count($item['users']) != $users->count()) {
                            $this->info($item['name'] . ': ' . count($item['users']) . ' ' . $users->count());
                        }
                        foreach ($users as $user) {
                            $user->product_groups()->sync([]);
                            $user->organizations()->sync([]);
                            $this->users[$user->id]['localities'][$locality->id] = $locality->id;

                            $gids = [];
                            foreach (($item['users'][$user->username]['groups'] ?? []) as $_gname) {
                                $gids[] = $this->product_groups[$_gname] ?? false;
                            }
                            $gids                             = array_filter($gids);
                            $this->users[$user->id]['groups'] = array_merge(($this->users[$user->id]['groups'] ?? []), $gids);
                            $this->users[$user->id]['status'] = $item['users'][$user->username]['status'] ?? '';
                        }
                    }
                }
            }

        }
    }

    function parseDivisionsMN()
    {
        $file = __DIR__ . '/tmmn.json';
        if (!File::exists($file)) {
            return;
        }
        $json = json_decode(File::get($file));
        foreach ($json as $item) {
            if ($item->khu_vuc) {
                $this->item_division = $item->khu_vuc;
            }
            if (empty($this->divisions[$this->item_division])) {
                $this->divisions[$this->item_division] = [
                    'name'     => $this->item_division,
                    'parent'   => 6,
                    'type'     => 4,
                    'asm'      => [],
                    'children' => [],
                ];
            }
            if ($item->asm_user) {
                $this->divisions[$this->item_division]['asm'][$item->asm_user] = $item->asm_user;
            }
//            if ($item->dia_ban) {
            if (empty($this->divisions[$this->item_division]['children'][$item->dia_ban])) {
                $province_id                                                       = $this->_findProvince($item->tinh);
                $this->divisions[$this->item_division]['children'][$item->dia_ban] = [
                    'name'      => $item->dia_ban,
                    'type'      => 5,
                    'province'  => $province_id,
                    'districts' => $this->_findDistricts($province_id, $item->quan),
                    'users'     => [],
                ];
            }
            foreach (['g1' => 'GR1', 'g2' => 'GR2', 'g3' => 'GR3'] as $g => $gname) {
                $username = $item->{"{$g}_username"} ?? '';
                if (!empty($username)) {
                    if (empty($this->divisions[$this->item_division]['children'][$item->dia_ban]['users'][$username])) {
                        $this->divisions[$this->item_division]['children'][$item->dia_ban]['users'][$username] = [
                            'groups' => [],
                            'status' => ''
                        ];
                    }
                    $this->divisions[$this->item_division]['children'][$item->dia_ban]['users'][$username]['groups'][$gname] = $gname;
                }
            }
//            }
        }
    }

    function parseDivisionsMB()
    {
        $file = __DIR__ . '/tmdata.json';
        if (!File::exists($file)) {
            return;
        }
        $json = json_decode(File::get($file));

        foreach ($json as $item) {
            if ($item->mien) {
                $this->item_mien = $item->mien;
            }
            if ($item->khu_vuc) {
                $this->item_division = $item->khu_vuc;
                $this->item_locality = $item->dia_ban;
                $this->item_giam_sat = $item->giam_sat;
            }

            if ($item->username) {
                if (empty($this->divisions[$this->item_division])) {
                    $this->divisions[$this->item_division] = [
                        'name'     => $this->item_division,
//                        'parent'   => $settings['parent'],
                        'parent'   => $this->mien[$this->item_mien],
                        'type'     => Organization::TYPE_KHU_VUC,
                        'asm'      => [],
                        'children' => [],
                    ];
                }
                if ($item->asm) {
                    $this->divisions[$this->item_division]['asm'][$item->username] = $item->username;
                }
                if ($item->dia_ban) {
                    if (empty($this->divisions[$this->item_division]['children'][$item->dia_ban])) {
                        $province_id                                                       = $this->_findProvince($item->tinh);
                        $this->divisions[$this->item_division]['children'][$item->dia_ban] = [
                            'name'      => $item->dia_ban,
                            'type'      => Organization::TYPE_DIA_BAN,
                            'province'  => $province_id,
                            'districts' => $this->_findDistricts($province_id, $item->quan),
                            'users'     => [],
                        ];
                    }
                    $groups = explode(',', $item->ten_nhom);
                    $groups = array_map('trim', $groups);
                    $groups = array_filter($groups);

                    $this->divisions[$this->item_division]['children'][$item->dia_ban]['users'][$item->username]['groups'] = $groups;
                    if ($item->trang_thai) {
                        $this->divisions[$this->item_division]['children'][$item->dia_ban]['users'][$item->username]['status'] = $item->trang_thai;
                    }
                }
            }
        }
//        dd($this->divisions);
    }

    function _findProvince($name): int
    {
        $province = $this->provinces
            ->where('province_name', $name)
            ->first();
        return $province?->id ?? 0;
    }

    function _findDistricts($province_id, $name): array
    {
        $name = str_replace(['1/3', '1/4', '3/4', '2/3'], '', $name);
        $name = explode(',', $name);
        $name = array_map('trim', $name);

        $districts = $this->districts
            ->where('province_id', $province_id)
            ->filter(function ($item) use ($name) {
                if (in_array($item->district_name, $name) || in_array($item->district_name_with_type, $name)) {
                    return true;
                }
                return false;
            });
        if ($districts->isNotEmpty()) {
            return $districts->pluck('id')->toArray();
        }

        return [];
    }
}
