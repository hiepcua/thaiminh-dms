<?php

namespace App\Console\Commands\Sync;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms:sync_users {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $users;

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
        $truncate = $this->option('truncate');
        if ($truncate) {
            DB::table('users')->truncate();
            DB::table('user_organization')->truncate();
            DB::table('user_product_group')->truncate();
        }
        $this->users = collect();
        $companies   = [
            [
                'company'   => 'tm',
                'file'      => 'v_user_tm.json',
                'map_roles' => [
                    1 => 'Admin',
                    2 => 'BOD',
                    3 => 'ASM',
                    4 => 'TDV',
                    5 => 'SaleAdmin',
                    6 => 'Marketing',
                ],
            ],
            [
                'company'   => 'pk',
                'file'      => 'v_user_pk.json',
                'map_roles' => [
                    1 => 'Admin',
                    2 => 'BOD',
                    3 => 'ASM',
                    4 => 'TDV',
                    5 => 'SaleAdmin',
                    6 => 'Marketing',
                ],
            ],
        ];

        foreach ($companies as $values) {
            $this->parseData($values);
        }
        $this->admin();

        try {
            foreach ($this->users as $user_attrs) {
                if (isset($user_attrs['id'])) {
                    $user = User::query()->firstOrCreate(['id' => $user_attrs['id']], $user_attrs);
                } else {
                    $user = User::query()->firstOrCreate(['email' => $user_attrs['email']], $user_attrs);
                }
                $user->syncRoles($user_attrs['group_ids']);
                echo '-';
            }
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        $this->newLine();

        return 0;
    }

    function parseData($values)
    {
        $json      = collect(json_decode(file_get_contents(__DIR__ . '/' . $values['file'])))->filter(function ($item) {
            return $item->type == 'table';
        })->first();
        $user_data = $json->data;
        foreach ($user_data as $user) {
            $created_at = strlen($user->created_at) < 10 ? null : Carbon::parse((int)$user->created_at)->format('Y-m-d H:i:s');
            $updated_at = strlen($user->updated_at) < 10 ? null : Carbon::parse((int)$user->updated_at)->format('Y-m-d H:i:s');
            $uid        = $values['company'] == 'pk' ? $user->info_id + 1000000000 : $user->info_id;
            $username   = $values['company'] == 'pk' ? $user->username . '_PK' : $user->username;
            $email      = $values['company'] == 'pk' ? $user->email . '_PK' : $user->email;

            $attributes = [
                'id'            => $uid,
                'name'          => $user->full_name ?? '',
                'email'         => $email,
                'username'      => $username,
                'password'      => $user->password_hash,
                'phone'         => substr($user->mobile, 0, 20),
                'dob'           => $user->birthday ?? '',
                'position'      => '',
                'agency_id'     => 0,
                'status'        => $user->active == 5 ? 1 : 0,
                'last_login_ip' => null,
                'last_login_at' => null,
                'change_pass'   => 0,
                'created_at'    => $created_at,
                'updated_at'    => $updated_at,
            ];
            $role_ids   = [];
            if ($user->group_ids) {
                $group_ids = explode(',', $user->group_ids);
                foreach ($group_ids as $group_id) {
                    $role_ids[] = $values['map_roles'][$group_id] ?? '';
                }
                $role_ids = array_filter($role_ids);
            }
            $attributes['group_ids'] = $role_ids;

            $this->users->add($attributes);
        }
    }

    function admin()
    {
        $user_attributes = [
            ['name' => 'TuyenDV', 'email' => 'tuyendv@tmp.vn',],
            ['name' => 'BangBD', 'email' => 'bangbd@tmp.vn',],
            ['name' => 'HuongKTT', 'email' => 'huongktt@tmp.vn',],
            ['name' => 'HopLV', 'email' => 'hoplv@tmp.vn',],
            ['name' => 'ThanhNV', 'email' => 'thanhnv@tmp.vn',],
            ['name' => 'HiepTV', 'email' => 'hieptv@tmp.vn',],
            ['name' => 'KhanhLD', 'email' => 'khanhld@tmp.vn',],
        ];

        foreach ($user_attributes as $attribute) {
            $attribute['username']    = $attribute['email'];
            $attribute['change_pass'] = 0;
            $attribute['status']      = 1;
            $attribute['password']    = Hash::make('12345a@A');
            $attribute['group_ids']   = [1];

            $this->users->add($attribute);
        }
    }
}
