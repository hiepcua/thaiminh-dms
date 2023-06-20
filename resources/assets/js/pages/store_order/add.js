window.orderFunc = {
    IS_MOBILE: window['orderArguments']['is_mobile'] ?? false,
    ROUTE_ORGANIZATION: window['orderArguments']['route_get_locality'] ?? '',
    ROUTE_CALC_PROMO: window['orderArguments']['route_calc_promo'] ?? '',
    PRODUCTS: window['orderArguments']['default_products'] || {},
    FORM_ELE: $('#form-store-orders'),
    ORGANIZATION_ELE: $('#form-organization_id'),
    CONTROL_ORDER_SETUP_AREA: $('.control-order-setup-area'),
    AGENCY_ELE: $('#form-agency_id'),
    STORE_ELE: $('#form-store_id'),
    ORDER_TYPE_ELE: $('#form-order_type'),
    ORDER_LOGISTIC_ELE: $('#form-order_logistic'),
    PRODUCT_TYPE_ELE: $('#form-product_type'),
    PRODUCT_ELE: $('#search-product'),
    QTY_ELE: $('#search-product-qty'),
    ADD_ELE: $('#search-add-product'),
    TABLE_ELE: $('#product-table'),
    TABLE_THEAD: window['orderArguments']['product_table_thead'] || '',
    TABLE_TFOOT: window['orderArguments']['product_table_tfoot'] || '',
    RESPONSE_GET_DATA_BY_LOCALITY: {},

    init: () => {
        let _that = orderFunc;
        if (_that.ROUTE_ORGANIZATION) {
            _that.selectInfoChange();
            // _that.controlShowSetupArea();
        }
        _that.formValidate();
        _that.formSubmit();
        _that.PRODUCT_ELE.on('select2:select', function () {
            _that.QTY_ELE.val(1).focus().select();
        });
        _that.ADD_ELE.on('click', function () {
            _that.add();
        });
        _that.QTY_ELE.focus(function () {
            $(this).select();
        });
        _that.QTY_ELE.on('keypress', function (e) {
            if (e.keyCode === 13) {//Enter
                _that.add();
            }
        });
        $(document).on('keyup', function (e) {
            if (e.keyCode === 113) {//F2
                _that.f2Event();
            }
        });
        $(document).on('change', '.row-product-qty', function () {
            let _tr = $(this).closest('tr')
                , _key = _tr.data('key')
                , _product_qty = parseInt($(this).val())
                , _product = _that.PRODUCTS[_key];

            let _values = {
                'qty': _product_qty,
                'price': _product['price'],
            };
            _that.update(_key, _values);

            _product = _that.PRODUCTS[_key];
            $('.row-product-amount', _tr).text(_product['amount_format']);

            _that.getPromotionItems();
            _that.renderTotal();
        });
        $(document).on('click', '.btn-remove', function () {
            _that.delete($(this));
        });
        $(document).on('click', '.btn-go-promo', function () {
            let _promo_id = $(this).data('promo_id');
            let _id = $(this).data('id');
            $(`input[name="promotions[${_promo_id}][${_id}]"]:first`).focus().select();
        });
        if (_that.countProducts()) {
            _that.renderTable();
        } else {
            _that.configTable();
        }

        $(document).on('click', '.promotion-condition .form-check-input', function () {
            _that.addPromotionCondition($(this));
        });
        if (_that.countProducts()) {
            _that.getPromotionItems();
        }

        if (_that.ORGANIZATION_ELE.val()) {
            _that.getDataByLocality(_that.ORGANIZATION_ELE.val());
        }
        _that.ORDER_TYPE_ELE.on('change', function () {
            if ($(this).val() === '2') {
                _that.AGENCY_ELE.prop('disabled', true);
            } else {
                _that.AGENCY_ELE.prop('disabled', false);
            }
        });
    },

    countProducts() {
        let _that = orderFunc;
        return Object.keys(_that.PRODUCTS).length;
    },

    formValidate() {
        let _that = orderFunc;
        _that.FORM_ELE.validate({
            focusInvalid: false,
            invalidHandler: function (form, validator) {
                if (!validator.numberOfInvalids()) {
                    return;
                }
                $('html, body').animate({
                    scrollTop: $(validator.errorList[0].element).offset().top
                }, 100);
            }
        });
    },

    formSubmit() {
        let _that = orderFunc;
        $('.btn-form-submit', _that.FORM_ELE).on('click', function () {
            let _spinner = $(this).find('.spinner-border');
            if (!_that.countProducts()) {
                _that.alert({message: 'Đơn hàng chưa có sản phẩm.'});
                return;
            }
            if (_that.FORM_ELE.valid()) {
                $(this).prop('disabled', true);
                _spinner.removeClass('hidden');
                _that.FORM_ELE.submit();
            }
        });
    },

    selectInfoChange: () => {
        let _that = orderFunc;
        _that.ORGANIZATION_ELE.on('change', function () {
            let _locality_id = $(this).val();
            tmp_theme.form_block(_that.FORM_ELE);

            _that.PRODUCTS = {};
            _that.renderTable();
            _that.getDataByLocality(_locality_id, 'ORGANIZATION');
        });
        //
        let _changeCallback = function () {
            let _locality_id = _that.ORGANIZATION_ELE.val();
            _that.getDataByLocality(_locality_id, 'STORE');
            // _that.controlShowSetupArea();
            let _agency_id = $("#form-agency_id").val();
            let _store_id = $("#form-store_id").val();
            if (_that.countProducts() && _agency_id && _store_id) {
                _that.getPromotionItems();
            }
        }
        _that.AGENCY_ELE.on('change', function () {
            _changeCallback();
        });
        _that.STORE_ELE.on('change', function () {
            _changeCallback();
        });
        _that.PRODUCT_TYPE_ELE.on('change', function () {
            _changeCallback();
        });
    },

    // controlShowSetupArea: () => {
    //     let _that = orderFunc;
    //
    //     _that.CONTROL_ORDER_SETUP_AREA.on('change', function () {
    //
    //         if ('messages' in _that.RESPONSE_GET_DATA_BY_LOCALITY && _that.RESPONSE_GET_DATA_BY_LOCALITY['messages'].length) {
    //             _that.alert({message: _that.RESPONSE_GET_DATA_BY_LOCALITY['messages'].join(', ')});
    //         }
    //
    //         tmp_theme.form_unblock(_that.FORM_ELE);
    //         $('.form-wrapper-promo input.form-control').focus(function () {
    //             $(this).select();
    //         });
    //     })
    // },

    getDataByLocality: (_locality_id, _type_change) => {
        let _that = orderFunc;
        ajax(_that.ROUTE_ORGANIZATION, 'GET', _that.FORM_ELE.serializeArray())
            .done(function (response) {
                _that.RESPONSE_GET_DATA_BY_LOCALITY = response;

                if (_type_change === 'ORGANIZATION') {
                    let _agencies = response['agencies'] ?? {};
                    let _stores = response['stores'] ?? {};

                    // agency
                    if (Object.keys(_agencies).length) {
                        _that.selectAddOptions(_that.AGENCY_ELE, _agencies, 'agency_id_option');
                    }
                    // store
                    if (Object.keys(_stores).length) {
                        _that.selectAddOptions(_that.STORE_ELE, _stores, 'store_id_option');
                    }

                    $('#wrapper-add-product').hide();
                    $('.form-wrapper-promo').hide();
                }
                if ('order_types' in response) {
                    _that.selectAddOptions(_that.ORDER_TYPE_ELE, response['order_types']);
                }
                if ('order_logistics' in response) {
                    _that.selectAddOptions(_that.ORDER_LOGISTIC_ELE, response['order_logistics'], '', response['order_logistic_default']);
                }
                if ('product_types' in response) {
                    _that.selectAddOptions(_that.PRODUCT_TYPE_ELE, response['product_types']);
                    _that.AGENCY_ELE.prop('disabled', false);
                }
                if ('order_bonus_info' in response) {
                    $('#wrapper-order_bonus_info').html(response['order_bonus_info']);
                }
                if ('product_options' in response) {
                    _that.selectAddOptions(_that.PRODUCT_ELE, response['product_options']);
                }

                if ('messages' in response && response['messages'].length) {
                    _that.alert({message: response['messages'].join(', ')});
                }
                // product
                let _show_product = _that.RESPONSE_GET_DATA_BY_LOCALITY['show_product'] || false;
                // console.log(_that.RESPONSE_GET_DATA_BY_LOCALITY['show_product'], _show_product);
                if (_show_product) {
                    $('#wrapper-add-product').show();
                    let _promotion_view = _that.RESPONSE_GET_DATA_BY_LOCALITY['promotion_view'] || '';
                    $('.promotion-wrapper').html(_promotion_view);
                    $('#accordionWrapperPromo').addClass('show');
                    if (_promotion_view) {
                        $('.form-wrapper-promo').show();
                    } else {
                        $('.form-wrapper-promo').hide();
                    }
                } else {
                    $('#wrapper-add-product').hide();
                    $('.form-wrapper-promo').hide();
                }
                tmp_theme.form_unblock(_that.FORM_ELE);
                tmp_theme.feather();
                tmp_theme.input_numeral_mask();
            })
            .fail(function (error) {
                tmp_theme.form_unblock(_that.FORM_ELE);
                console.log(error);
                _that.alert({message: 'Server has an error. Please try again!'});
            });
    },
    selectAddOptions: (ele, objectOptions, optionClass = '', defaultValue = '') => {
        if (defaultValue === '') {
            defaultValue = ele.val();
        }
        if (optionClass) {
            $(`.${optionClass}`, ele).remove();
        } else {
            $('option', ele).remove();
        }
        if (typeof objectOptions === 'string') {
            ele.append(objectOptions);
        } else {
            $.each(objectOptions, function (_id, _name) {
                ele.append(`<option class="${optionClass}" ${defaultValue == _id ? 'selected' : ''} value="${_id}">${_name}</option>`);
            });
        }
    },
    addPromotionCondition: (_ele) => {
        let _that = orderFunc
            , _wrapper = _ele.closest('.promo-item')
            , _wrapper_promotion = _ele.closest('.promotion')
            , _wrapper_condition = _ele.closest('.promotion-condition')
            , _current_number = parseInt(_wrapper_promotion.attr('data-number'))
            , _is_checked = _ele.is(':checked')
        ;

        if (_is_checked) {
            _current_number += 1;
            _wrapper_condition.attr('data-order', _current_number);
        } else {
            _wrapper_condition.removeAttr('data-order');
        }
        _wrapper_promotion.attr('data-number', _current_number);
        // $('span.badge', _wrapper_condition).text(_current_number);

        let orders = {};
        $.each($('.promotion-condition', _wrapper_promotion), function (_i, _item) {
            let _checkbox = $('.form-check-input', _item);
            if (_checkbox.is(':checked')) {
                orders[_item.getAttribute('data-order')] = _checkbox.val();
            }
        });
        $('.promotion-value', _wrapper).val(JSON.stringify(orders));
        //
        _that.getPromotionItems();
    },
    getPromotionItems: () => {
        let _that = orderFunc
            , _values = _that.FORM_ELE.serializeArray();
        tmp_theme.form_block(_that.FORM_ELE);
        $('div[id^="promo-info-"]').html('');

        ajax(_that.ROUTE_CALC_PROMO, 'post', _values)
            .done(function (promoValues) {
                _that.PRODUCTS = Object.fromEntries(Object.entries(_that.PRODUCTS).filter(([, _value]) => {
                    return parseInt(_value['promo_id']) === 0;
                }));
                $.each(promoValues, function (_key, promoValue) {
                    let _countItem = Object.keys(promoValue['data']['items']).length;
                    if (_countItem) {
                        $.each(promoValue['data']['items'], function (_key, _values) {
                            delete _that.PRODUCTS[_key];
                            _that.create(_values['id'], _values);
                        });
                    }
                    _that.renderPromoInfo(promoValue);
                });
                _that.renderTable();
                tmp_theme.form_unblock(_that.FORM_ELE);
            })
            .fail(function (error) {
                if ('status' in error && error['status'] === 422) {
                    let _message = [];
                    Object.values(error['responseJSON']['errors']).map(_error => {
                        if (typeof _error == 'object') {
                            _message.push(_error[0])
                        }
                    });
                    _that.alert({'message': _message.join(',')});
                } else {
                    console.log(error);
                }
                tmp_theme.form_unblock(_that.FORM_ELE);
            });
    },
    renderPromoInfo: (promoValue) => {
        let _wrapper = $(`#promo-info-${promoValue['promo_id']}-${promoValue['condition_id']}`)
            , _alert_body = $('<div class="alert-body"></div>');
        _wrapper.removeClass()
        _wrapper.html('');

        if (promoValue['data']['message'].length) {
            $.each(promoValue['data']['message'], function (_key, _message) {
                _alert_body.append(`<div>${_message}</div>`);
            });
        }
        _wrapper.addClass(`alert alert-${promoValue['data']['type']} mt-0 mb-1`);
        _wrapper.append(_alert_body);
    },
    f2Event: () => {
        let _that = orderFunc;
        _that.PRODUCT_ELE.select2('open');
    },
    add: () => {
        let _that = orderFunc;
        let _option_selected = $('option:selected', _that.PRODUCT_ELE);
        let _product_id = _that.PRODUCT_ELE.val();
        let _product_qty = parseInt(_that.QTY_ELE.val());
        let _product_name = _option_selected.data('name');
        let _product_price = parseInt(_option_selected.data('price'));
        let _product_point = parseInt(_option_selected.data('point'));

        if (!_product_id) {
            _that.alert({message: 'Sản phẩm chưa được chọn.'});
            return;
        }
        if (!_product_qty || _product_qty < 1) {
            _that.alert({message: 'Số lượng chưa được nhập.'});
            return;
        }

        let _index = `product_${_product_id}`;
        if (_index in _that.PRODUCTS) {
            // edit
            let _values = _that.PRODUCTS[_index];
            if (_values['qty'] !== _product_qty) {
                _values['qty'] = _product_qty;

                _that.update(_index, _values);
            }
        } else {
            // create
            let _values = {
                'name': _product_name,
                'qty': _product_qty,
                'price': _product_price,
                'point': _product_point,
                'type': 'product',
                'sort': 2,
            };

            _that.create(_product_id, _values);
        }

        _that.renderTable();

        if (_that.countProducts() > 0) {
            _that.getPromotionItems();
        }
    },
    create: (_product_id, _values) => {
        let _that = orderFunc;
        let _amount = _values['qty'] * _values['price'];
        let _key = 'key' in _values ? _values['key'] : `product_${_product_id}`;

        _that.PRODUCTS[_key] = {
            'key': _key,
            'promo_id': 'promo_id' in _values ? _values['promo_id'] : 0,
            'condition_id': 'condition_id' in _values ? _values['condition_id'] : 0,
            'promo_name': 'promo_name' in _values ? _values['promo_name'] : '',
            'condition_name': 'condition_name' in _values ? _values['condition_name'] : '',
            'id': _product_id,
            'name': _values['name'],
            'qty': _values['qty'],
            'price': _values['price'],
            'point': _values['point'],
            'type': _values['type'],
            'sort': _values['sort'],
            'amount': _amount,
            'details': 'details' in _values ? _values['details'] : '',
            'price_format': _that.formatAmount(_values['price']),
            'amount_format': _that.formatAmount(_amount),
        };
    },
    update: (_index, _values) => {
        let _that = orderFunc;
        let _amount = _values['qty'] * _values['price'];

        _that.PRODUCTS[_index]['qty'] = _values['qty'];
        _that.PRODUCTS[_index]['amount'] = _amount;
        _that.PRODUCTS[_index]['amount_format'] = _that.formatAmount(_amount);
    },
    delete: (_ele) => {
        let _that = orderFunc;
        let _tr = _ele.closest('tr')
            , _key = _tr.data('key');
        delete _that.PRODUCTS[_key];
        _that.renderTable();
        _that.getPromotionItems();
    },
    configTable: () => {
        let _that = orderFunc;
        if (!_that.countProducts()) {
            _that.TABLE_ELE.hide();
        } else {
            _that.TABLE_ELE.show();
        }
        _that.TABLE_ELE.find('thead tr').remove();
        _that.TABLE_ELE.find('tfoot tr').remove();
        _that.TABLE_ELE.find('thead').append(_that.TABLE_THEAD);
        _that.TABLE_ELE.find('tfoot').append(_that.TABLE_TFOOT);
    },
    sortProducts: (_products) => {
        return Object.entries(_products).sort(([, a], [, b]) => {
            return a['sort'] === b['sort'] ? 0 : (a['sort'] < b['sort'] ? -1 : 1);
        });
    },
    renderTable: () => {
        let _that = orderFunc
            , _sort_items = _that.sortProducts(_that.PRODUCTS)
            , _i = 1;
        _that.configTable();
        _that.TABLE_ELE.find('tbody tr').remove();
        $.each(_sort_items, function (_index, _values) {
            _that.renderRow(_i, _values[0], _values[1]);
            _i++;
        });
        _that.renderTotal();
        _that.touchspin();
        tmp_theme.feather();
    },
    renderTotal: () => {
        let _that = orderFunc;
        let _total_qty = 0, _total_discount = 0, _total_amount = 0;
        $.each(Object.values(_that.PRODUCTS), (i, _value) => {
            if (_value['type'] === 'discount') {
                _total_discount += parseInt(_value['amount']);
            } else {
                _total_qty += parseInt(_value['qty']);
                _total_amount += parseInt(_value['amount']);
            }
        });

        $('.row-total-qty').text(_that.formatAmount(_total_qty));
        $('.row-total-amount').text(_that.formatAmount(_total_amount - _total_discount));
        $('.row-subtotal-amount').text(_that.formatAmount(_total_discount));
        if (_total_discount) {
            $('.row-subtotal').removeClass('hidden');
        } else {
            $('.row-subtotal').addClass('hidden');
        }
    },
    renderRow: (_i, _index, _values) => {
        let _that = orderFunc
            , _is_hidden = _values['type'] === 'discount' ? 'hidden' : ''
            , _promo_star = ''
            , _input_qty_class = ''
            , _icon_gift = ''
            , _btn_row_class = 'text-danger btn-remove'
            , _btn_feather = 'trash-2';

        if (_values['promo_id']) {
            _btn_row_class = 'hidden';
            _btn_feather = 'edit';
            _input_qty_class = 'disabled-touchspin';

            if (_values['type'] === 'product') {
                _promo_star = `<i data-feather='star' class="text-warning"></i>`;
                _btn_row_class = 'text-info btn-go-promo';
            } else if (_values['type'] === 'gift') {
                _promo_star = '';
                _icon_gift = `<i data-feather='gift' class="text-success"></i>`;
            }
        }
        let _template;
        if (_that.IS_MOBILE) {
            _template = `<tr class="${_is_hidden}" data-key="${_index}">
                <td>
                    <div class="d-flex mb-1 justify-content-between align-items-center">
                        <div>
                            ${_icon_gift}${_values['name']} - <b>${_values['price_format']}</b>
                            <div>${_values['promo_name'] ? ` (${_values['promo_name']})` : ''} ${_promo_star}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon ${_btn_row_class}"
                            data-promo_id="${_values['promo_id']}"
                            data-id="${_values['id']}"
                            style="flex: 0 0 36px;">
                            <i data-feather="${_btn_feather}"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                    <div class="input-group ${_input_qty_class}">
                        <input type="number" name="products[${_index}][qty]" value="${_values['qty']}"
                        class="form-control row-product-qty text-center touchspin"
                        min="1"
                        max="999"
                        ${_values['promo_id'] ? `readonly` : ''}
                        placeholder="Nhập số lượng">
                    </div>
                    <b class="row-product-amount">${_values['amount_format']}</b>
                    </div>
                    <input type="hidden" name="products[${_index}][key]" value="${_values['key']}">
                    <input type="hidden" name="products[${_index}][promo_id]" value="${_values['promo_id']}">
                    <input type="hidden" name="products[${_index}][condition_id]" value="${_values['condition_id']}">
                    <input type="hidden" name="products[${_index}][promo_name]" value="${_values['promo_name']}">
                    <input type="hidden" name="products[${_index}][condition_name]" value="${_values['condition_name']}">
                    <input type="hidden" name="products[${_index}][name]" value="${_values['name']}">
                    <input type="hidden" name="products[${_index}][price]" value="${_values['price']}">
                    <input type="hidden" name="products[${_index}][point]" value="${_values['point']}">
                    <input type="hidden" name="products[${_index}][type]" value="${_values['type']}">
                    <input type="hidden" name="products[${_index}][id]" value="${_values['id']}">
                    <textarea class="hidden" name="products[${_index}][details]">${_values['details']}</textarea>
                </td>
            </tr>`;
        } else {
            _template = `<tr class="${_is_hidden}" data-key="${_index}">
                <td class="text-center">${_i}</td>
                <td>
                    ${_values['name']}
                    ${_values['promo_name'] ? ` (${_values['promo_name']})` : ''}
                    ${_promo_star}
                    <input type="hidden" name="products[${_index}][key]" value="${_values['key']}">
                    <input type="hidden" name="products[${_index}][promo_id]" value="${_values['promo_id']}">
                    <input type="hidden" name="products[${_index}][condition_id]" value="${_values['condition_id']}">
                    <input type="hidden" name="products[${_index}][promo_name]" value="${_values['promo_name']}">
                    <input type="hidden" name="products[${_index}][condition_name]" value="${_values['condition_name']}">
                    <input type="hidden" name="products[${_index}][name]" value="${_values['name']}">
                    <input type="hidden" name="products[${_index}][price]" value="${_values['price']}">
                    <input type="hidden" name="products[${_index}][point]" value="${_values['point']}">
                    <input type="hidden" name="products[${_index}][type]" value="${_values['type']}">
                    <input type="hidden" name="products[${_index}][id]" value="${_values['id']}">
                    <textarea class="hidden" name="products[${_index}][details]">${_values['details']}</textarea>
                </td>
                <td class="">
                    <input type="number" name="products[${_index}][qty]" value="${_values['qty']}"
                        class="form-control row-product-qty text-end"
                        min="1"
                        max="999"
                        pattern="\\d"
                        onKeyPress="if(this.value.length==3) return false;"
                        ${_values['promo_id'] ? `readonly` : ''}
                        style="width: 100px;margin-left: auto;">
                </td>
                <td class="text-end">${_values['price_format']}</td>
                <td class="text-end"><b class="row-product-amount">${_values['amount_format']}</b></td>
                <td class="text-end">
                    <button type="button" class="btn btn-icon ${_btn_row_class}"
                        data-promo_id="${_values['promo_id']}"
                        data-id="${_values['id']}">
                        <i data-feather="${_btn_feather}"></i>
                    </button>
                </td>
            </tr>`;
        }
        _that.TABLE_ELE.find('tbody').append(_template);
    },
    formatAmount: (_amount) => {
        return new Intl.NumberFormat('vi-VN').format(_amount);
    },
    alert: (_options) => {
        let _message = _options['message'] ?? ''
            , _type = _options['type'] ?? 'warning'
            , _title = _options['title'] ?? 'Warning!';

        Swal.fire({
            title: _title,
            text: _message,
            icon: _type,
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    },
    touchspin: () => {
        $('.touchspin').TouchSpin({
            min: 1,
            max: 999,
            buttondown_class: 'btn btn-primary',
            buttonup_class: 'btn btn-primary',
            buttondown_txt: feather.icons['minus'].toSvg(),
            buttonup_txt: feather.icons['plus'].toSvg()
        });
    }
};
$(document).ready(function () {
    $('.flatpickr-basic').flatpickr();
    orderFunc.init();
});
