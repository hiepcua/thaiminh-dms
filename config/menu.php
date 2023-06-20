<?php

return [
    'default' => [
        [
            'group' => true,
            'name'  => 'Tổng quan',
            'route' => 'admin.dashboard.index',
        ],
        [
            'name'  => '',
            'group' => true,
            'child' => [
                [
                    'name'  => '1. Nhà thuốc',
                    'icon'  => '<i data-feather="home"></i>',
                    'child' => [
                        [
                            'name'    => '1.1 Đơn của NT',
                            'route'   => 'admin.store-orders.index',
                            'perm'    => 'xem_don_hang_nha_thuoc',
                            'pattern' => ['store-orders/create', 'store-orders/*/*'],
                            'child'   => [
                                [
                                    'name'    => '1.1 Thêm đơn',
                                    'route'   => 'admin.store-orders.create',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '1.2 DS đơn TT Key',
                            'route'   => 'admin.key-reward-order.index',
                            'perm'    => ['list_key_reward_order'],
                            'pattern' => ['key-reward-order/*'],
                            'child'   => [
                                [
                                    'name'    => '1.2 Chi tiết đơn TT Key',
                                    'route'   => 'admin.key-reward-order.detail',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '1.3 DS nhà thuốc',
                            'route'   => 'admin.stores.index',
                            'perm'    => ['xem_nha_thuoc', 'tdv_xem_nha_thuoc'],
                            'pattern' => ['stores/create', 'stores/*/*'],
                            'child'   => [
                                [
                                    'name'    => '1.3 Thêm mới nhà thuốc',
                                    'route'   => 'admin.stores.create',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.3 Sửa nhà thuốc',
                                    'route'   => 'admin.stores.edit',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.2 Chi tiết nhà thuốc',
                                    'route'   => 'admin.stores.show',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '1.4 DS NT mới',
                            'route'   => 'admin.new-stores.index',
                            'perm'    => 'xem_nha_thuoc_moi',
                            'pattern' => ['new-stores/create', 'new-stores/*/*', 'new-stores/*'],
                            'child'   => [
                                [
                                    'name'    => '1.4 Duyệt nhà thuốc mới',
                                    'route'   => 'admin.new-stores.approve',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.4 Chi tiết nhà thuốc mới',
                                    'route'   => 'admin.new-stores.show',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.3 Sửa nhà thuốc mới',
                                    'route'   => 'admin.new-stores.edit',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '1.5 DS NT thay đổi',
                            'route'   => 'admin.store_changes.index',
                            'perm'    => 'xem_nha_thuoc_thay_doi',
                            'pattern' => ['store_changes/create', 'store_changes/*/*', 'store_changes/*'],
                            'child'   => [
                                [
                                    'name'    => '1.5 Duyệt nhà thuốc thay đổi',
                                    'route'   => 'admin.store_changes.approve',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.5 Chi tiết nhà thuốc thay đổi',
                                    'route'   => 'admin.store_changes.show',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.5 Sửa nhà thuốc thay đổi',
                                    'route'   => 'admin.store_changes.edit',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '1.6 DS Tuyến',
                            'route'   => 'admin.lines.index',
                            'perm'    => 'xem_tuyen',
                            'pattern' => ['lines/create', 'lines/*/*', 'lines/*'],
                            'child'   => [
                                [
                                    'name'    => '1.6 Thêm mới tuyến',
                                    'route'   => 'admin.lines.create',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.6 Sửa tuyến',
                                    'route'   => 'admin.lines.edit',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '1.7 DS NT treo poster',
                            'route'   => 'admin.posters-agency.index',
                            'perm'    => 'xem_nha_thuoc_treo_poster',
                            'pattern' => [
                                'posters-agency/*',
                                'posters-agency/*/*',
                                'posters-agency/*/*/*'
                            ],
                        ],
                        [
                            'name'    => '1.8 NT thay đổi tuyến',
                            'route'   => 'admin.line-store-change.index',
                            'perm'    => 'xem_nha_thuoc_thay_doi_tuyen',
                            'pattern' => [
                                'line-store-change/*',
                                'line-store-change/*/*',
                                'line-store-change/*/*/*'
                            ],
                            'child'   => [
                                [
                                    'name'    => '1.8 Duyệt nhà thuốc thay đổi tuyến',
                                    'route'   => 'admin.line-store-change.edit',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.8 Chi tiết nhà thuốc thay đổi tuyến',
                                    'route'   => 'admin.line-store-change.show',
                                    'display' => false
                                ],
                            ]
                        ],

                    ]
                ],
            ],
        ],
        [
            'name'  => '',
            'group' => true,
            'child' => [
                [
                    'name'  => '2. Hợp đồng KEY',
                    'icon'  => '<i data-feather="file-text"></i>',
                    'child' => [
                        [
                            'name'    => '2.1 Quản lý hạng',
                            'route'   => 'admin.ranks.index',
                            'perm'    => 'xem_danh_sach_vai_tro',
                            'pattern' => ['ranks/create', 'ranks/*/*', 'ranks/*'],
                            'child'   => [
                                [
                                    'name'    => '2.1 Thêm hạng',
                                    'route'   => 'admin.ranks.create',
                                    'display' => false
                                ],
                                [
                                    'name'    => '2.1 Sửa hạng',
                                    'route'   => 'admin.ranks.edit',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '2.2 QL HĐ Key',
                            'route'   => 'admin.revenue-periods.index',
                            'perm'    => 'xem_ql_hop_dong_key',
                            'pattern' => ['revenue-periods/create', 'revenue-periods/*/*'],
                        ],
                    ]
                ],
            ],
        ],
        [
            'name'  => '',
            'group' => true,
            'child' => [
                [
                    'name'  => '3. Đại lý',
                    'icon'  => '<i data-feather="file-text"></i>',
                    'child' => [
                        [
                            'name'    => '3.1 Danh sách',
                            'route'   => 'admin.agency.index',
                            'perm'    => 'xem_danh_sach_dai_ly',
                            'pattern' => ['agency/create', 'agency/*/*', 'agency/*'],
                            'child'   => [
                                [
                                    'name'    => '3.1 Thêm đại lý',
                                    'route'   => 'admin.agency.create',
                                    'display' => false
                                ],
                                [
                                    'name'    => '3.1 Sửa đại lý',
                                    'route'   => 'admin.agency.show',
                                    'display' => false
                                ]
                            ],
                        ],
                        [
                            'name'    => '3.2 Đơn nhập',
                            'route'   => 'admin.agency-order.index',
                            'perm'    => 'xem_danh_sach_don_nhap_dai_ly',
                            'pattern' => ['agency-order/', 'agency-order/*/*', 'agency-order/*'],
                            'child'   => [
                                [
                                    'name'    => '3.2 Sale admin lên đơn cho đại lý',
                                    'route'   => 'admin.agency-order.create',
                                    'display' => false
                                ],
                                [
                                    'name'    => '3.2 Xem đơn nhập bởi TDV',
                                    'route'   => 'admin.agency-order.show-order-tdv',
                                    'display' => false
                                ],
                                [
                                    'name'    => '3.2 Xem thông tin sale admin lên đơn đại lý',
                                    'route'   => 'admin.agency-order.show',
                                    'display' => false
                                ],
                                [
                                    'name'    => '3.2 Sửa thông tin sale admin lên đơn đại lý',
                                    'route'   => 'admin.agency-order.edit',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '3.3 Đơn nhập TDV',
                            'route'   => 'admin.agency-order-tdv.index',
                            'perm'    => 'xem_danh_sach_don_nhap_cua_tdv_dai_ly',
                            'pattern' => ['agency-order-tdv/', 'agency-order-tdv/*/*', 'agency-order-tdv/*'],
                            'child'   => [
                                [
                                    'name'    => '3.3 Xác nhận đơn ĐL thanh toán',
                                    'route'   => 'admin.agency-order-tdv.validate-before-create',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '3.4 Hàng tồn',
                            'route'   => 'admin.agency-inventory.index',
                            'perm'    => 'xem_danh_sach_hang_ton_dai_ly',
                            'pattern' => ['agency-inventory/', 'agency-inventory/*/*', 'agency-inventory/*']
                        ],
                    ]
                ],
            ],
        ],
        [
            'name'  => '',
            'group' => true,
            'child' => [
                [
                    'name'  => '5. Báo cáo',
                    'icon'  => '<i data-feather="file-text"></i>',
                    'child' => [
                        [
                            'name'  => '5.1 Doanh số',
                            'group' => true,
                            'child' => [
                                [
                                    'name'  => '5.1.1 Doanh số',
                                    'route' => 'admin.report.revenue.tdv',
                                    'perm'  => 'xem_bao_cao_doanh_thu_tdv',
//                                    'perm'  => 'xem_bao_cao_doanh_thu_tdv',
                                    'child' => [
                                        [
                                            'name'    => '5.1.1 Chi tiết SP',
                                            'route'   => 'admin.report.revenue.tdv.detail',
                                            'perm'    => 'xem_bao_cao_doanh_thu_chi_tiet_tdv',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'  => '5.1.2 Doanh thu nhà thuốc',
                                    'route' => 'admin.report.pharmacy-revenue',
                                    'perm'  => 'xem_bao_cao_doanh_thu_nha_thuoc',
                                    'child' => [
                                        [
                                            'name'    => 'Chi tiết doanh thu',
                                            'route'   => 'admin.report.pharmacy-revenue.detail',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'  => '5.1.3 Thưởng Key',
                                    'route' => 'admin.report.revenue.store.key_qc',
                                    'perm'  => 'xem_bao_cao_thuong_key_qc',
                                ],
                            ]
                        ],
                        [
                            'name'  => '5.2 Đại lý',
                            'group' => true,
                            'child' => [
                                [
                                    'name'    => '5.2.1 Bảng kê ĐL',
                                    'route'   => 'admin.report.agency-orders',
                                    'perm'    => 'xem_danh_sach_don_nhap_cua_tdv_dai_ly',
                                    'pattern' => ['agency-orders/', 'agency-orders/*/*', 'agency-orders/*'],
                                ],
                                [
                                    'name'    => '5.2.2 Bản in PXK',
                                    'route'   => 'admin.report.print_pxk',
                                    'perm'    => 'xem_danh_sach_ban_in_phieu_xuat_kho',
                                    'pattern' => ['print-pxk/', 'print-pxk/*/*', 'print-pxk/*'],
                                ]
                            ]
                        ],
                        [
                            'name'  => '5.3 TDV',
                            'group' => true,
                            'child' => [
                                [
                                    'name'  => '5.3.1 LS Checkin',
                                    'route' => 'admin.checkin.histories',
                                    'perm'  => 'xem_danh_sach_tdv_checkin',
                                ],
                            ]
                        ],
                    ]
                ],
            ],
        ],
        [
            'name'  => '',
            'group' => true,
            'child' => [
                [
                    'name'  => '4. Poster',
                    'icon'  => '<i data-feather="image"></i>',
                    'child' => [

                    ],
                ],

            ]
        ],
        [
            'name'  => '',
            'group' => true,
            'child' => [
                [
                    'name'  => '10. Quản lý',
                    'icon'  => '<i data-feather="settings"></i>',
                    'child' => [
                        [
                            'name'  => '10.1 Người dùng',
                            'child' => [
                                [
                                    'name'    => '10.1.1 Người dùng',
                                    'route'   => 'admin.users.index',
                                    'perm'    => 'xem_danh_sach_nguoi_dung',
                                    'pattern' => [
                                        'users/*',
                                        'users/*/*',
                                        'users/*/*/*',
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.1.1 Thêm người dùng',
                                            'route'   => 'admin.users.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.1.1 Sửa người dùng',
                                            'route'   => 'admin.users.edit',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'    => '10.1.2 Vai trò',
                                    'route'   => 'admin.roles.index',
                                    'perm'    => 'xem_danh_sach_vai_tro',
                                    'pattern' => [
                                        'roles/*',
                                        'roles/*/*',
                                        'roles/*/*/*',
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.1.2 Thêm vai trò',
                                            'route'   => 'admin.roles.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.1.2 Sửa vai trò',
                                            'route'   => 'admin.roles.edit',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'    => '10.1.3 Quyền',
                                    'route'   => 'admin.permissions.index',
                                    'perm'    => 'xem_danh_sach_quyen',
                                    'pattern' => [
                                        'permissions/*',
                                        'permissions/*/*',
                                        'permissions/*/*/*'
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.1.3 Sửa quyền',
                                            'route'   => 'admin.permissions.edit',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'    => '10.1.4 Cây sơ đồ',
                                    'route'   => 'admin.organizations.index',
                                    'perm'    => 'xem_danh_sach_vai_tro',
                                    'pattern' => [
                                        'organizations/*',
                                        'organizations/*/*',
                                        'organizations/*/*/*',
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.1.4 Thêm cây sơ đồ',
                                            'route'   => 'admin.organizations.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.1.4 Sửa cây sơ đồ',
                                            'route'   => 'admin.organizations.edit',
                                            'display' => false
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        [
                            'name'  => '10.2 Sản phẩm',
                            'child' => [
                                [
                                    'name'    => '10.2.1 DS SP',
                                    'route'   => 'admin.products.index',
                                    'perm'    => 'xem_danh_sach_vai_tro',
                                    'pattern' => [
                                        'products/*',
                                        'products/*/*',
                                        'products/*/*/*'
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.2.1 Thêm Sản Phẩm',
                                            'route'   => 'admin.products.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.2.1 Sửa Sản Phẩm',
                                            'route'   => 'admin.products.edit',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'    => '10.2.2 Ưu tiên',
                                    'route'   => 'admin.product-group-priorities.index',
                                    'perm'    => 'xem_danh_sach_vai_tro',
                                    'pattern' => [
                                        'product-group-priorities/*',
                                        'product-group-priorities/*/*',
                                        'product-group-priorities/*/*/*'
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.2.2 Lịch sử ưu tiên của sản phẩm',
                                            'route'   => 'admin.product-group-priorities.history',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.2.2 Thêm nhóm và sản phẩm ưu tiên',
                                            'route'   => 'admin.product-group-priorities.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.2.2 Sửa nhóm và sản phẩm ưu tiên',
                                            'route'   => 'admin.product-group-priorities.edit',
                                            'display' => false
                                        ],
                                    ]
                                ],
                                [
                                    'name'    => '10.2.3 Nhóm SP',
                                    'route'   => 'admin.product-groups.index',
                                    'perm'    => 'xem_danh_sach_vai_tro',
                                    'pattern' => [
                                        'product-groups/*',
                                        'product-groups/*/*',
                                        'product-groups/*/*/*'
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.2.3 Thêm mới mhóm sản phẩm',
                                            'route'   => 'admin.product-groups.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.2.3 Sửa mhóm sản phẩm',
                                            'route'   => 'admin.product-groups.edit',
                                            'display' => false
                                        ],
                                    ]
                                ],
                            ]
                        ],
                        [
                            'name'  => '10.3 CT KM',
                            'child' => [
                                [
                                    'name'    => '10.3.1 Quà tặng',
                                    'route'   => 'admin.gift.index',
                                    'perm'    => 'xem_danh_sach_qua_tang',
                                    'pattern' => [
                                        'gift/*', 'gift/*/*', 'gift/*/*/*'
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.3.1 Thêm quà tặng',
                                            'route'   => 'admin.gift.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.3.1 Sửa quà tặng',
                                            'route'   => 'admin.gift.show',
                                            'display' => false
                                        ]
                                    ]
                                ],
                                [
                                    'name'    => '10.3.2 CT KM',
                                    'route'   => 'admin.promotion.index',
                                    'perm'    => 'xem_danh_sach_chuong_trinh_khuyen_mai',
                                    'pattern' => [
                                        'promotion/*',
                                        'promotion/*/*',
                                        'promotion/*/*/*'
                                    ],
                                    'child'   => [
                                        [
                                            'name'    => '10.3.2 Thêm chương trình Khuyến mãi',
                                            'route'   => 'admin.promotion.create',
                                            'display' => false
                                        ],
                                        [
                                            'name'    => '10.3.2 Sửa chương trình khuyến mãi',
                                            'route'   => 'admin.promotion.show',
                                            'display' => false
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name'    => '10.4 CT treo poster',
                            'route'   => 'admin.posters.index',
                            'perm'    => 'xem_chuong_trinh_treo_poster',
                            'pattern' => [
                                'posters/*',
                                'posters/*/*',
                                'posters/*/*/*'
                            ],
                            'child'   => [
                                [
                                    'name'    => '10.4 Chương trình Poster theo sản phẩm',
                                    'route'   => 'admin.posters.create',
                                    'display' => false
                                ],
                                [
                                    'name'    => '1.5.1 Sửa chương trình Poster',
                                    'route'   => 'admin.posters.edit',
                                    'display' => false
                                ],
                            ]
                        ],
                        [
                            'name'    => '10.5 Cấu hình hệ thống',
                            'route'   => 'admin.system-variable.setting',
                            'perm'    => 'setting_thong_so_he_thong',
                            'pattern' => [
                                'system-variable/*',
                                'system-variable/*/*',
                                'system-variable/*/*/*'
                            ],
                        ],
                    ],
                ],

            ]
        ],
    ]
];
