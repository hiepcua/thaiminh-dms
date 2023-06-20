<?php

return [
    'default_paginate' => 20,
    'option_paginate'  => [20, 30, 50, 100, 200, 500],
    'pages'            => [
        'agency-order-list'           => [
            'headers'       => [
                'checkbox'       => "<input type='checkbox' class='form-check-input' id='checkAll'>",
                'agency_name'    => 'Đại lý',
                'address_info'   => 'Địa chỉ',
                'date_info'      => 'Ngày<br>Ngày tạo/Ngày TT',
                'type'           => 'Loại đơn',
                'status'         => 'Trạng thái',
                'note'           => 'Ghi chú',
                'item_texts'     => 'Sản phẩm',
                'total_discount' => 'CK',
                'total_amount'   => 'TT',
                'features'       => 'Thao tác',
            ],
            'classCustom'   => [
                'note'           => 'text-truncate mxw-200-px',
                'item_texts'     => 'mw-250-px',
                'division_name'  => 'mw-100-px',
                'status'         => 'mw-150-px justify-content-center',
                'date_info'      => 'text-nowrap text-center',
                'total_amount'   => 'text-nowrap text-end',
                'total_discount' => 'text-nowrap text-end',
            ],
            'customColumns' => [
                'date_info',
                'address_info',
                'checkbox',
                'features',
                'agency_name',
                'item_texts',
                'status',
                'total_amount',
                'total_discount',
                'type'
            ],
            'sortColumn'    => [
                'updated_at',
                'booking_at',
                'agency_name',
                'status',
                'total_amount',
            ],
            'centerColumn'  => [
                'checkbox',
                'division_name',
                'type',
                'status',
                'item_texts',
                'features',
            ]
        ],
        'agency-list'                 => [
            'headers'       => [
                'name'       => 'Tên đại lý',
                'code'       => 'Mã',
                'localities' => 'Địa bàn',
                'address'    => 'Địa chỉ',
                'features'   => 'Thao tác',
            ],
            'customColumns' => [
                'features',
                'localities'
            ],
            'sortColumn'    => [
                'code',
                'name',
            ],
            'centerColumn'  => [
                'features'
            ]
        ],
        'store-list'                  => [
            'headers'       => [
                'stt'          => 'STT',
                'code'         => 'Mã',
                'locality'     => 'Địa bàn',
                'tdv'          => 'TDV',
                'name'         => 'Tên',
                'address'      => 'Địa chỉ',
                'customStatus' => 'Trạng thái',
                'features'     => 'Thao tác',
            ],
            'customColumns' => [
                'customStatus',
                'features',
                'tdv',
            ],
            'sortColumn'    => [
                'name',
                'code',
            ],
            'centerColumn'  => [
                'stt',
                'code',
                'customStatus',
                'features',
            ],
            'styleCss'      => [
                'tdv' => 'width: 150px'
            ]
        ],
        'checkin-history-list'        => [
            'headers'       => [
                'stt'         => 'STT',
                'user_name'   => 'TDV',
                'store_name'  => 'Nhà thuốc',
                'checkin_at'  => 'Checkin',
                'checkout_at' => 'Checkout',
            ],
            'customColumns' => [
                'checkout_at',
            ],
            'sortColumn'    => [
                'checkin_at',
                'checkout_at',
            ],
            'centerColumn'  => [
                'stt',
            ],
            'classCustom'   => [
                'checkin_at'  => 'text-nowrap',
                'checkout_at' => 'text-nowrap',
            ]
        ],
        'checkin-history-tdv-list'    => [
            'headers'       => [
                'stt'        => 'STT',
                'store_info' => 'Nhà thuốc',
            ],
            'customColumns' => [
                'store_info',
            ],
            'centerColumn'  => [
                'stt',
            ],
        ],
        'request-checkout-list'       => [
            'headers'       => [
                'stt'           => 'STT',
                'creator_name'  => 'Người tạo',
                'reviewer_name' => 'Người duyệt',
                'store_name'    => 'Nhà thuốc',
                'created_at'    => 'Ngày đề xuất',
                'checkin_at'    => 'Checkin',
                'note'          => 'Ghi chú',
                'status'        => 'Trạng thái',
                'action'        => 'Thao tác',
            ],
            'customColumns' => [
                'action',
                'status',
                'note',
            ],
            'centerColumn'  => [
                'stt',
            ],
        ],
        'request-checkout-list-tdv'   => [
            'headers'       => [
                'stt'           => 'STT',
                'reviewer_name' => 'Người duyệt',
                'store_name'    => 'Nhà thuốc',
                'created_at'    => 'Ngày đề xuất',
                'checkin_at'    => 'Checkin',
                'note'          => 'Ghi chú',
                'status'        => 'Trạng thái',
            ],
            'customColumns' => [
                'action',
                'status',
                'note',
            ],
            'centerColumn'  => [
                'stt',
            ],
        ],
        'tdv-store-list'              => [
            'headers'       => [
                'store_info' => 'Thông tin nhà thuốc'
            ],
            'customColumns' => [
                'store_info',
            ],
        ],
        'new_store-list'              => [
            'headers'       => [
                'stt'               => 'STT',
                'code'              => 'Mã',
                'name'              => 'Tên',
                'address'           => 'Địa chỉ',
                'phone_owner'       => 'SĐT',
                'created_by'        => 'TDV',
                'created_at_format' => 'Thời gian tạo',
                'status'            => 'Trạng thái',
                'updated_by'        => 'Duyệt NT',
                'features'          => 'Chức năng',
            ],
            'customColumns' => [
                'status',
                'features',
            ],
            'sortColumn'    => [
                'name',
                'code',
            ],
            'centerColumn'  => [
                'stt',
                'code',
                'phone_owner',
                'status',
                'created_by',
                'updated_by',
                'created_at_format',
                'features',
            ]
        ],
        'agency-tdv-order-list'       => [
            'headers'       => [
                'checkbox'              => "<input type='checkbox' class='form-check-input check-allow-create-order' id='checkAll'>",
                'code'                  => 'Mã số đơn NT',
                'order_code'            => 'Mã số đơn TT',
                'sale_name'             => 'TDV',
                'agency_info'           => 'Đại lý',
                'store_info'            => 'Nhà thuốc',
                'create_and_booking_at' => 'Ngày<br>Ngày tạo/Ngày TT',
                'status'                => 'Trạng thái<br>Đơn/TT',
                'item_texts'            => 'Sản phẩm',
                'discount'              => 'CK',
                'total_amount'          => 'TT',
                'note'                  => 'Ghi chú',
            ],
            'classCustom'   => [
                'item_texts'            => 'mw-250-px',
                'sale_name'             => ' text-nowrap',
                'create_and_booking_at' => ' text-center text-nowrap',
                'status'                => ' text-center text-nowrap',
                'order_code'            => ' text-center text-nowrap',
                'discount'              => ' text-end text-nowrap mw-100-px',
                'total_amount'          => ' text-end text-nowrap mw-100-px',
            ],
            'customColumns' => [
                'code',
                'sale_name',
                'agency_info',
                'store_info',
                'order_code',
                'create_and_booking_at',
                'checkbox',
                'item_texts',
                'status',
                'total_amount',
                'discount',
                'sale_name'
            ],
            'sortColumn'    => [
                'agency_name',
                'status',
                'agency_status',
                'discount',
                'total_amount',
            ],
            'centerColumn'  => [
                'checkbox',
                'type',
                'status',
                'features'
            ]
        ],
        'store-order-list'            => [
            'headers'       => [
                'checkbox'              => '<input type="checkbox" class="form-check-input" id="checkAll">',
                'store_code'            => 'Mã NT/ Mã TT key',
                'store_name'            => 'Tên NT',
                'agency_name'           => 'Tên đại lý',
                'code'                  => 'Mã đơn',
                'booking_delivery_text' => 'Ngày đặt/<br>Ngày giao',
                'col_status'            => 'Trạng thái',
                'col_product'           => 'Sản phẩm',
                'col_discount'          => 'CK',
                'col_total_amount'      => 'Tổng TT',
            ],
            'customColumns' => [
                'checkbox',
                'col_status',
                'col_discount',
                'col_product',
                'store_name',
                'booking_delivery_text',
            ],
            'sortColumn'    => [],
            'centerColumn'  => [
                'checkbox',
                'col_status',
            ],
            'classCustom'   => [
//                'col_discount'     => 'text-end',
                'col_total_amount' => 'text-end',
                'col_product'      => 'col_product',
            ],
        ],
        'store-order-list-tdv'        => [
            'headers'       => [
                'store_tdv' => 'Đơn hàng nhà thuốc',
            ],
            'customColumns' => [
                'store_tdv',
            ],
            'sortColumn'    => [],
            'centerColumn'  => []
        ],
        'statement_for_agency'        => [
            'headers'       => [
                'locality'     => 'Địa bàn',
                'store_code'   => 'Mã NT',
                'store_name'   => 'Tên NT',
                'address'      => 'Địa chỉ',
                'phone'        => 'SĐT',
                'tdv_name'     => 'TDV',
                'delivery_at'  => 'Ngày giao',
                'sub_total'    => 'Doanh số',
                'discount'     => 'Chiết khấu',
                'total_amount' => 'Phải thu',
                'agency_name'  => 'Đại lý',
                'note'         => 'Ghi chú',
            ],
            'customColumns' => [
                'sub_total',
                'discount',
                'total_amount',
            ],
            'sortColumn'    => [
                'sub_total',
                'discount',
                'total_amount',
                'booking_at'
            ],
            'classCustom'   => [
                'locality'     => 'text-center mw-150-px',
                'store_name'   => 'text-center mw-200-px',
                'address'      => 'text-center mw-240-px',
                'tdv_name'     => 'text-center mw-200-px',
                'delivery_at'  => 'text-center mw-120-px',
                'sub_total'    => 'text-end mw-150-px',
                'discount'     => 'text-end mw-150-px',
                'total_amount' => 'text-end mw-150-px',
                'agency_name'  => 'text-center mw-200-px',
                'note'         => 'text-center mw-200-px',
            ],
        ],
        'product-list'                => [
            'headers'       => [
                'stt'             => 'STT',
                'name'            => 'Tên SP',
                'code'            => 'Mã SP',
                'company_name'    => 'Công Ty',
                'wholesale_price' => 'Giá buôn',
                'price'           => 'Giá khuyến nghị',
                'status'          => 'Trạng thái',
                'features'        => 'Chức năng',
            ],
            'customColumns' => [
                'name',
                'status',
                'features',
            ],
            'sortColumn'    => [
                'name',
                'price',
                'wholesale_price',
            ],
            'centerColumn'  => [
                'stt',
                'code',
                'company_name',
                'price',
                'wholesale_price',
                'status',
                'features',
            ]
        ],
        'agency-order-file-list'      => [
            'headers'       => [
                'stt'        => 'STT',
                'tdv_name'   => 'Người TT',
                'basic_info' => 'Thông tin chung',
                'item_qty'   => 'SL Sản Phẩm',
                'cost'       => 'Thành Tiền',
                'discount'   => 'Chiết Khấu',
                'final_cost' => 'Thanh Toán',
                'file_url'   => 'Phiếu xuất kho'
            ],
            'customColumns' => [
                'basic_info',
                'cost',
                'discount',
                'final_cost',
                'file_url'
            ],
            'sortColumn'    => [
                'cost',
                'discount',
                'final_cost',
            ],
            'centerColumn'  => [
                'stt',
                'order_code_prefix',
                'qty_store_order_merged',
                'item_qty',
                'file_url'
            ],
            'classCustom'   => [
                'cost'       => 'text-end',
                'discount'   => 'text-end',
                'final_cost' => 'text-end',
            ],
        ],
        'product-group-priority-list' => [
            'headers'       => [
                'stt'           => 'STT',
                'updated_at'    => 'Ngày update',
                'product'       => 'Sản phẩm',
                'user_fullname' => 'Người tạo',
                'sub_group'     => 'Nhóm',
                'periods'       => 'Chu kỳ',
                'priority'      => 'Ưu tiên',
                'features'      => 'Chức năng',
            ],
            'customColumns' => [
                'product',
                'periods',
                'priority',
                'features',
            ],
            'sortColumn'    => [
                'updated_at',
                'priority',
            ],
            'centerColumn'  => [
                'stt',
                'updated_at',
                'user_fullname',
                'sub_group',
                'periods',
                'priority',
                'features',
            ]
        ],
        'product-group-list'          => [
            'headers'       => [
                'stt'               => 'STT',
                'name'              => 'Tên',
                'product_type_name' => 'Loại Hàng',
                'parent'            => 'Cấp cha',
                'note'              => 'Mô tả',
                'status'            => 'Trạng thái',
                'features'          => 'Chức năng',
            ],
            'customColumns' => [
                'name',
                'note',
                'status',
                'features',
            ],
            'sortColumn'    => [
                'name',
                'parent',
            ],
            'centerColumn'  => [
                'stt',
                'status',
                'features',
            ]
        ],
        'store-change-list'           => [
            'headers'       => [
                'stt'               => 'STT',
                'code'              => 'Mã',
                'name'              => 'Tên',
                'address'           => 'Địa chỉ',
                'phone_owner'       => 'SĐT',
                'created_by'        => 'TDV',
                'created_at_format' => 'Thời gian tạo',
                'status'            => 'Trạng thái',
                'updated_by'        => 'Duyệt thay đổi',
                'features'          => 'Chức năng',
            ],
            'customColumns' => [
                'status',
                'features',
            ],
            'sortColumn'    => [
                'code',
                'name',
            ],
            'centerColumn'  => [
                'stt',
                'status',
                'features',
                'created_by',
                'created_at_format',
                'updated_by',
                'phone_owner',
            ]
        ],
        'tdv-new-stores-list'         => [
            'headers'       => [
                'store_info' => 'Thông tin nhà thuốc'
            ],
            'customColumns' => [
                'store_info',
            ],
        ],
        'tdv-store-changes-list'      => [
            'headers'       => [
                'store_info' => 'Thông tin nhà thuốc'
            ],
            'customColumns' => [
                'store_info',
            ],
        ],
        'report-store-key-qc'         => [
            'headers'       => [
                'col_store' => 'Thông tin NT',
            ],
            'customColumns' => [],
            'classCustom'   => [],
        ],
        'poster-list'                 => [
            'headers'       => [
                'stt'            => 'STT',
                'name'           => 'Chương trình',
                'product_name'   => 'Sản phẩm',
                'image'          => 'Ảnh',
                'date_apply'     => 'Ngày áp dụng',
                'reward'         => 'Số lượng trả thưởng',
                'accptance_list' => 'Ngày nghiệm thu',
                'zones_list'     => 'Khu vực',
                'action'         => 'Tác vụ',
            ],
            'customColumns' => [
                'name',
                'zones_list',
                'accptance_list',
                'action',
                'image'
            ],
            'sortColumn'    => [
                'name',
            ],
            'centerColumn'  => [
                'stt',
            ]
        ],
        'poster-pharmacy'             => [
            'headers'       => [
                'stt'           => 'STT',
                'pharmacy_name' => 'NT',
                'poster_name'   => 'Poster',
                'tdv_name'      => 'TDV',
                'status'        => 'Trạng thái',
                'list_images'   => 'Ảnh',
                'reward'        => 'Thưởng',
            ],
            'customColumns' => [
                'pharmacy_name',
                'list_images',
                'action'
            ],
            'sortColumn'    => [
                'name',
            ],
            'centerColumn'  => [
                'stt',
            ]
        ],
        "line-store-list"             => [
            "headers"       => [
                "stt"               => "STT",
                "store_name"        => "Tên nhà thuốc",
                "line_name"         => "Tên tuyến",
                "number_visit"      => "Số lần thăm/tháng",
                "locality"          => "Địa bàn",
                "created_at_format" => "Thời gian tạo",
                "status"            => "Trạng thái",
                "user_updated"      => "Duyệt thay đổi",
                "features"          => "Chức năng",
            ],
            "customColumns" => [
                "created_at_format",
                "status",
                "features"
            ],
            "sortColumn"    => [
            ],
            "centerColumn"  => [
                "stt",
                "number_visit",
                "created_at_format",
                "user_updated",
                "status",
                "features",
            ]
        ],
        "line-list"                   => [
            "headers"       => [
                "locality"       => "Địa bàn",
                "line_name"      => "Tên tuyến",
                "weekday"        => "Thứ",
                "tdv"            => "TDV",
                "store_name"     => "Nhà thuốc",
                "number_visit"   => "Số lần thăm/tháng",
                "latest_checkin" => "Ngày ghé thăm gần nhất",
                "latest_booking" => "Ngày nhập hàng gần nhất",
                "features"       => "Thao tác",
            ],
            "customColumns" => [
                "weekday",
                "store_name",
                "number_visit",
                "features"
            ],
            "sortColumn"    => [
            ],
            "centerColumn"  => [
                "number_visit",
                "latest_checkin",
                "latest_booking",
                "features",
            ]
        ],
        'revenue-pharmacy-list'       => [
            'headers'       => [
                'stt'                   => 'STT',
                'pharmacy_name'         => 'Tên NT',
                'pharmacy_code'         => 'Mã nhà thuốc',
                'pharmacy_organization' => 'Địa bàn',
                'pharmacy_district'     => 'Quận/Huyện',
                'pharmacy_province'     => 'Tỉnh/Thành phố',
                'region'                => 'Miền',
                'total_order'           => 'Tổng đơn hàng',
                'total_sub_amount'      => 'Tổng doanh thu',
                'total_discount'        => 'Tổng chiết khấu',
                'total_amount'          => 'Tổng doanh thu sau chiết khấu',
            ],
            'customColumns' => [
                'pharmacy_name',
                'total_order'
            ],
            'sortColumn'    => [
                'name',
            ],
            'centerColumn'  => [
                'stt',
            ]
        ],
        'detail-revenue-pharmacy'     => [
            'headers'       => [
                'stt'          => 'STT',
                'product_name' => 'Sản phẩm & quà tặng',
                'product_qty'  => 'Số lượng sp đã bán',
                'sub_total'    => 'Tổng doanh thu',
                'discount'     => 'Tổng chiết khấu',
                'total_amount' => 'Tổng doanh thu sau chiết khấu',
            ],
            'customColumns' => [
                'product_name',
            ],
            'sortColumn'    => [
                'name',
            ],
            'centerColumn'  => [
                'stt',
            ]
        ],

        "agency-inventory-list"   => [
            "headers"       => [
                "agency_info"   => "Đại lý",
                "product_name"  => "Sản phẩm",
                "start_num"     => "Tồn đầu",
                "import_num"    => "Nhập",
                "export_num"    => "Xuất",
                "inventory_num" => "Tồn (lý thuyết)",
                "status"        => "Trạng thái",
                "features"      => "Thao tác",
            ],
            "customColumns" => [
                "agency_info",
                "status",
                "features"
            ],
            "centerColumn"  => [
                "status",
                "features",
            ],
            'classCustom'   => [
                'start_num'     => 'text-end',
                'import_num'    => 'text-end',
                'export_num'    => 'text-end',
                'inventory_num' => 'text-end',
            ],
        ],
        'key-reward-order'        => [
            'headers'       => [
                'checkbox'         => '<input type="checkbox" class="form-check-input" id="checkAll">',
                'pharmacy_name'    => 'Nhà thuốc',
                'pharmacy_address' => 'Địa chỉ',
                'logistic_text'    => 'Đơn vị ship/<br>Trạng thái',
                'date_ship_text'   => 'Ngày nhập/<br>Ngày ship',
                'order_code'       => 'Mã đơn gộp',
                'products'         => 'Sản phẩm',
                'sum_total_amount' => 'Tổng thanh toán',
            ],
            'customColumns' => [
                'checkbox',
                'pharmacy_name',
                'col_status',
                'products',
                'logistic_text',
                'date_ship_text',
            ],
            'sortColumn'    => [],
            'centerColumn'  => [
                'checkbox',
                'col_status',
            ],
            'classCustom'   => [
                'col_discount'     => 'text-end',
                'sum_total_amount' => 'text-end',
                'products'         => 'col_product',
            ],
        ],
        'detail-key-reward-order' => [
            'headers'       => [
                'booking_at'       => 'Ngày lên đơn',
                'tdv'              => 'Trình dược viên',
                'products'         => 'Sản phẩm',
                'sum_total_amount' => 'Tổng thanh toán',
            ],
            'customColumns' => [
                'checkbox',
                'products',
            ],
            'sortColumn'    => [],
        ],

        'revenue-pharmacy-list' => [
            'headers'       => [
                'stt'                   => 'STT',
                'pharmacy_name'         => 'Tên NT',
                'pharmacy_code'         => 'Mã nhà thuốc',
                'pharmacy_organization' => 'Địa bàn',
                'pharmacy_district'     => 'Quận/Huyện',
                'pharmacy_province'     => 'Tỉnh/Thành phố',
                'region'                => 'Miền',
                'total_order'           => 'Tổng đơn hàng',
                'total_sub_amount'      => 'Tổng doanh thu',
                'total_discount'        => 'Tổng chiết khấu',
                'total_amount'          => 'Tổng doanh thu sau chiết khấu',

            ],
            'customColumns' => [
                'pharmacy_name',
                'total_order'
            ],
            'sortColumn'    => [
                'name',
            ],
            'centerColumn'  => [
                'stt',
            ]
        ],

        'detail-revenue-pharmacy' => [
            'headers'       => [
                'stt'          => 'STT',
                'product_name' => 'Sản phẩm & quà tặng',
                'product_qty'  => 'Số lượng sp đã bán',
                'sub_total'    => 'Tổng doanh thu',
                'discount'     => 'Tổng chiết khấu',
                'total_amount' => 'Tổng doanh thu sau chiết khấu',

            ],
            'customColumns' => [
                'product_name',
            ],
            'sortColumn'    => [
                'name',
            ],
            'centerColumn'  => [
                'stt',
            ]
        ],
    ],
];
