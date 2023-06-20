<?php

namespace Database\Seeders;

use App\Models\SystemVariable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $guard_name = 'web';
        $role_names = ['Admin', 'BOD', 'SaleAdmin', 'BM', 'ASM', 'TDV', 'Marketing'];
        foreach ($role_names as $_name) {
            Role::findOrCreate($_name, $guard_name);
        }

        $systemVariables = [
            'Chức năng checkin' => [
                [
                    'name'         => SystemVariable::DISTANCE_ALLOW_CHECKIN,
                    'display_name' => 'Bán kính cho phép TDV checkin/checkout',
                    'value'        => 200
                ],
                [
                    'name'         => SystemVariable::LIMIT_FORGET_CHECKIN_A_MONTH,
                    'display_name' => 'Giới hạn quên checkout 1 tháng',
                    'value'        => 3
                ],
            ]
        ];

        foreach ($systemVariables as $functionName => $variables) {
            foreach ($variables as $variable) {
                $variable['function'] = $functionName;
                $oldVariable          = SystemVariable::where('name', $variable['name'])->first();

                if ($oldVariable) {
                    continue;
                }

                SystemVariable::create($variable);
            }
        }

        $group_perms = [
            '1.1' => [
                'xem don hang nha thuoc'   => [1, 3, 5, 6,],
                'them don hang nha thuoc'  => [1, 3, 5, 6,],
                'doi trang thai giao hang' => [1, 3, 5,],
                'doi trang thai xoa don'   => [1, 3, 5,],
                'tdv xem don hang nt'      => [6,],
            ],
            '1.2' => [
                'list_key_reward_order'        => [1,],
                'list_key_reward_export_excel' => [1,],
                'change_ship_status'           => [1,],
            ],

            '1.3'    => [
                'xem nha thuoc'            => [1, 3],
                'them nha thuoc'           => [1, 3],
                'sua nha thuoc'            => [1, 3],
                'xem nha thuoc moi'        => [1, 3, 6],
                'duyet nha thuoc moi'      => [1, 3],
                'xem nha thuoc thay doi'   => [1, 3, 6],
                'duyet nha thuoc thay doi' => [1, 3],
                'tdv xem nha thuoc'        => [6,],
                'tdv them nha thuoc'       => [6,],
                'tdv sua nha thuoc'        => [6,],
            ],
            '1.6'    => [
                'xem tuyen'  => [1, 3],
                'them tuyen' => [1, 3],
                'sua tuyen'  => [1, 3],
                'xoa tuyen'  => [1, 3],
            ],
            '1.7'    => [
                'xem chuong trinh treo poster'  => [1,],
                'them chuong trinh treo poster' => [1,],
                'sua chuong trinh treo poster'  => [1,],
                'xem nha thuoc treo poster'     => [1, 3, 6],
                'download_nt_treo_poster'       => [1,],
                'poster_pharmacy'               => [1,],
            ],
            '1.8'    => [
                'xem nha thuoc thay doi tuyen'   => [1, 5],
                'duyet nha thuoc thay doi tuyen' => [1, 5],
            ],
            '2.1'    => [
                'xem hang'  => [1,],
                'them hang' => [1,],
                'sua hang'  => [1,],
            ],
            '2.2'    => [
                'xem_ql_hop_dong_key'  => [1,],
                'them_ql_hop_dong_key' => [1,],
                'sua_ql_hop_dong_key'  => [1,],
            ],
            '3.2'    => [
                'xem_danh_sach_don_nhap_dai_ly' => [1,],
                'huy_don_nhap_dai_ly'           => [1,],
                'sua_don_nhap_dai_ly'           => [1,],
                'xem_chi_tiet_don_nhap_dai_ly'  => [1,],
                'agency_order'                  => [1,],
                'them_don_nhap_dai_ly'          => [1,],
            ],
            '3.3'    => [
                'xem_danh_sach_don_nhap_cua_tdv_dai_ly' => [1,],
                'agency_order_tdv'                      => [1,],
                'them_don_nhap_tdv_toi_dai_ly'          => [1,],
            ],
            '3.4'    => [
                'xem_danh_sach_hang_ton_dai_ly' => [1,],
                'download_danh_sach_hang_ton_dai_ly' => [1,],
                'agency_inventory' => [1,],
            ],
            '5.1.1'  => [
                'xem bao cao doanh thu tdv'               => [1, 3,],
                'download bao cao doanh thu tdv'          => [1, 3,],
                'xem bao cao doanh thu chi tiet tdv'      => [1, 3,],
                'download bao cao doanh thu chi tiet tdv' => [1, 3,],
            ],
            '5.1.2'  => [
                'xem bao cao doanh thu nha thuoc'      => [1, 3,],
                'download bao cao doanh thu nha thuoc' => [1, 3,],
            ],
            '5.1.3'  => [
                'xem bao cao thuong key qc'      => [1, 3,],
                'download bao cao thuong key qc' => [1, 3,],
            ],
            '5.2.1'  => [
                'xem_bao_cao_ban_ke_dai_ly' => [1,],
                'download_ban_ke_dai_ly'    => [1,],
            ],
            '5.2.2'  => [
                'xem_danh_sach_ban_in_phieu_xuat_kho' => [1,],
                'agency_order_files'                  => [1,],
            ],
            '5.3.1'  => [
                'xem_danh_sach_tdv_checkin'           => [1, 3, 5],
                'xem_danh_sach_de_xuat_quen_checkout' => [1, 3, 5],
                'tao_de_xuat_quen_checkout'           => [1, 3, 5],
                'duyet_de_xuat_quen_checkout'         => [1, 3, 5],
            ],
            '10.1.1' => [
                'xem danh sach nguoi dung' => [1,],
                'them nguoi dung'          => [1,],
                'sua nguoi dung'           => [1,],
                'chon cay so do'           => [2, 3, 5, 6,],
                'chon nhom san pham'       => [6,],
                'lien ket dai ly'          => [6,],
                'switch_user'              => [1,],
            ],
            '10.1.2' => [
                'xem danh sach vai tro' => [1,],
                'them vai tro'          => [1,],
                'sua vai tro'           => [1,],
            ],
            '10.1.3' => [
                'xem danh sach quyen' => [1,],
                'them quyen'          => [1,],
                'sua quyen'           => [1,],
                'xoa quyen'           => [1,],
            ],
            '10.1.4' => [
                'xem cay so do'         => [1,],
                'them cay so do'        => [1,],
                'sua cay so do'         => [1,],
                'loc du lieu cay so do' => [3, 5, 6,],
            ],
            '10.2.1' => [
                'xem san pham'  => [1,],
                'them san pham' => [1,],
                'sua san pham'  => [1,],
            ],
            '10.2.2' => [
                'xem nhom va san pham uu tien'  => [1,],
                'them nhom va san pham uu tien' => [1,],
                'sua nhom va san pham uu tien'  => [1,],
                'xoa nhom va san pham uu tien'  => [1,],
            ],
            '10.2.3' => [
                'xem nhom san pham'  => [1,],
                'them nhom san pham' => [1,],
                'sua nhom san pham'  => [1,],
                'xoa nhom san pham'  => [1,],
            ],
            '10.3.1' => [
                'xem danh sach dai_ly' => [1,],
                'them dai ly'          => [1,],
                'sua dai ly'           => [1,],
                'xoa dai ly'           => [1,],
            ],
            '10.4'   => [
                'xem danh sach qua_tang' => [1,],
                'them qua tang'          => [1,],
                'sua qua tang'           => [1,],
                'xoa qua tang'           => [1,],
            ],
            '10.4.2' => [
                'xem danh sach chuong trinh khuyen mai' => [1,],
                'them chuong trinh khuyen mai'          => [1,],
                'sua chuong trinh khuyen mai'           => [1,],
                'xoa chuong trinh khuyen mai'           => [1,],
            ],
            '10.5'   => [
                'setting thong so he thong' => [1,],
            ],
            'tdv'    => [
                'tdv_xem_danh_sach_nha_thuoc_checkin'     => [1, 6],
                'tdv_checkin_tai_nha_thuoc'               => [1, 6],
                'tdv_xem_lich_su_checkin'                 => [1, 6],
                'tdv_xem_danh_sach_de_xuat_quen_checkout' => [1, 6],
                'tdv_tao_de_xuat_quen_checkout'           => [1, 6],
            ]
        ];

        foreach ($group_perms as $group => $perms) {
            if (!$perms) {
                continue;
            }

            foreach ($perms as $perm => $role_ids) {
                $snake_case_perm = Str::lower(Str::snake(Str::replace(['/', ',', '(', ')', '-'], ' ', $perm)));
                $permission      = Permission::query()
                    ->where('name', $snake_case_perm)
                    ->where('guard_name', $guard_name)
                    ->first();
                if (!$permission) {
                    $permission = Permission::create([
                        'group'      => $group,
                        'name'       => $snake_case_perm,
                        'name_2'     => $perm,
                        'guard_name' => $guard_name
                    ]);
                    $permission->syncRoles($role_ids);
                }
                if ($permission->group != $group) {
                    $permission->update([
                        'group' => $group,
                    ]);
                }
            }
        }
    }
}
