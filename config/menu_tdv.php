<?php

return [
    'name'  => 'Chức năng',
    'group' => true,
    'child' => [
        [
            'name'  => 'Tổng quan',
            'route' => 'admin.tdv.dashboard',
        ],
        [
            'name'  => 'Đơn hàng nhà thuốc',
            'route' => 'admin.store-orders.index',
            'perm'  => 'xem_don_hang_nha_thuoc',
            'child' => [
                [
                    'name'    => 'Thêm mới đơn hàng',
                    'route'   => 'admin.store-orders.create',
                    'display' => false
                ],
            ]
        ],
        [
            'name'  => 'Danh sách nhà thuốc',
            'route' => 'admin.tdv.store.index',
            'perm'  => ['tdv_xem_nha_thuoc', 'xem_nha_thuoc'],
            'child' => [
                [
                    'name'    => 'Doanh thu nhà thuốc',
                    'route'   => 'admin.tdv.store.turnover',
                    'display' => false
                ],
                [
                    'name'    => 'Thông tin nhà thuốc',
                    'route'   => 'admin.tdv.store.show',
                    'display' => false
                ],
                [
                    'name'    => 'Thêm mới nhà thuốc',
                    'route'   => 'admin.tdv.store.create',
                    'display' => false
                ],
                [
                    'name'    => 'Sửa nhà thuốc',
                    'route'   => 'admin.tdv.store.edit',
                    'display' => false
                ],
            ]
        ],
        [
            'name'  => 'DS nhà thuốc mới',
            'route' => 'admin.tdv.new-stores.index',
            'perm'  => 'xem_nha_thuoc_moi',
            'child' => [
                [
                    'name'    => 'Chi tiết nhà thuốc mới',
                    'route'   => 'admin.tdv.new-stores.show',
                    'display' => false
                ]
                ,[
                    'name'    => 'Sửa nhà thuốc mới',
                    'route'   => 'admin.tdv.new-stores.edit',
                    'display' => false
                ],
            ]
        ],
        [
            'name'  => 'DS NT thay đổi',
            'route' => 'admin.tdv.store-changes.index',
            'perm'  => 'xem_nha_thuoc_thay_doi',
            'child' => [
                [
                    'name'    => 'Chi tiết nhà thuốc thay đổi',
                    'route'   => 'admin.tdv.store-changes.show',
                    'display' => false
                ],
                [
                    'name'    => 'Sửa nhà thuốc thay đổi',
                    'route'   => 'admin.tdv.store-changes.edit',
                    'display' => false
                ],
            ]
        ],
        [
            'name'  => 'DS NT treo poster',
            'route' => 'admin.tdv.register-poster.list',
            'perm'  => 'xem_nha_thuoc_treo_poster',
        ],
        [
            'name'  => 'Checkin',
            'route' => 'admin.tdv.checkin.list',
            'perm'  => 'tdv_xem_danh_sach_nha_thuoc_checkin',
        ],
        [
            'name'  => 'LS Checkin',
            'route' => 'admin.tdv.checkin.histories',
            'perm'  => 'tdv_xem_lich_su_checkin',
        ],
    ]
];
