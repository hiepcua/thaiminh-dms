$(document).ready(function () {
    let _have_parent = $('#form-have_parent'),
        _no_parent = $('#form-no_parent'),
        _vat_parent = $('#vat_from_parent'),
        _box_parent_store = $('#box-parent-store'),
        _parent_id = $('#form-parent_id'),
        _parent_name = $('#form-parent_name'),
        _province = $('#form-province'),
        _ward = $('#form-ward'),
        _locality = $('#form-organization_id'),
        _storeType = $('#form-type'),
        _storeCode = $('#form-code');

    _have_parent.click(function () {
        if ($(this).is(':checked')) {
            _box_parent_store.show();
            _vat_parent.removeAttr('disabled');
        }
    })

    _no_parent.click(function () {
        if ($(this).is(':checked')) {
            _box_parent_store.hide();
            _vat_parent.attr('disabled', 'disabled');
            _vat_parent.prop('checked', false);
        }
    })

    _vat_parent.on('click', (e) => {
        let parent_id = _parent_id.val();
        if (_vat_parent.is(':checked')) {
            if (parent_id) {
                ajax(ROUTE_GET_STORE_BY_ID, 'POST', {
                    store_id: parent_id
                }).done(function (response) {
                    if (response) {
                        $('#form-vat_buyer').val(response['vat_buyer'] ?? '');
                        $('#form-vat_company').val(response['vat_company'] ?? '');
                        $('#form-vat_number').val(response['vat_number'] ?? '');
                        $('#form-vat_email').val(response['vat_email'] ?? '');
                        $('#form-vat_address').val(response['vat_address'] ?? '');
                    }
                    $('#form-vat_buyer, #form-vat_company, #form-vat_number, #form-vat_email, #form-vat_address').attr('readonly', true);
                }).fail(function (error) {
                    console.log(error);
                    alert('Server has an error. Please try again!');
                });
            } else {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    text: 'Chưa chọn nhà thuốc cha!'
                })
            }
        } else {
            $('#form-vat_buyer, #form-vat_company, #form-vat_number, #form-vat_email, #form-vat_address').attr('readonly', false);
        }
    });

    _province.on('change', () => {
        let province_id = _province.val();

        ajax(ROUTE_GET_LOCALITY_PROVINCE, 'POST', {
            province_id: province_id
        }).done(function (response) {
            $("#form-organization_id").html(response.htmlString);
        }).fail(function (error) {
            console.log(error);
            alert('Server has an error. Please try again!');
        });
    })

    _locality.on('change', (e) => {
        let locality_id = e.currentTarget.value;

        list_users(locality_id);
    });

    let list_users = (locality_id) => {
        if (locality_id > 0) {
            ajax(ROUTE_GET_USER_BY_LOCALITY, 'POST', {
                locality_id: locality_id
            }).done(function (response) {
                $('#list-tdv').html(response.htmlString);
            }).fail(function (error) {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        }
    }

    if (IS_EDIT) {
        list_users(_locality.val());
    }

    if (!IS_TDV && IS_CREATE) {
        _storeType.on('change', function () {
            _storeCode.val('');
        });

        _province.on('change', function () {
            _storeCode.val('');
        });

        $('#form-district').on('change', function () {
            let storeType = _storeType.val(),
                provinceId = _province.val(),
                districtId = $(this).val();

            ajax(ROUTE_GENERATION_STORE_CODE, 'POST', {
                provinceId: provinceId,
                districtId: districtId,
                storeType: storeType,
            }).done(function (response) {
                $('#form-code').val(response);
            }).fail(function (error) {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });
    }

    let duplicateStore = () => {
        ajax(ROUTE_GET_STORE_DUPLICATE, 'POST', {
            name: $('#form-name').val(),
            code: $('#form-code').val(),
            address: $('#form-address').val(),
            phone_owner: $('#form-phone_owner').val(),
            vat_number: $('#form-vat_number').val(),
            locality: _locality.val(),
            wardId: _ward.val(),
            excludeId: STORE_ID
        }).done(function (response) {
            if (response.length > 0) {
                let _html = '';
                let stt = 1;
                Object.entries(response).forEach(([key, value]) => {
                    _html += '<div class="mb-1 text-start">';
                    _html += stt + '/ ';
                    _html += value;
                    _html += '</div>';
                    stt++;
                });
                _html += '<p class="text-start">Nhà thuốc phải được SA duyệt mới hiển thị lên danh sách nhà thuốc</p>';

                Swal.fire({
                    title: "Danh sách các nhà thuốc có thể trùng.",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Tiếp tục lưu',
                    cancelButtonText: 'Xem lại',
                    html: _html
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#form-add-edit-store').submit();
                    }
                })
            } else {
                $('#form-add-edit-store').submit();
            }
        }).fail(function (error) {
            console.log(error);
            alert('Server has an error. Please try again!');
        });
    }

    $('#form-btn-save').on('click', function (e) {
        if (validForm()) {
            if (validAddress()) {
                duplicateStore();
            } else {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Trường địa chỉ đang nhập thừa dữ liệu: Phường/ xã, Quận/ Huyện, Tỉnh/ TP',
                    showCloseButton: true,
                    showConfirmButton: false,
                });
            }
            e.preventDefault();
        } else {
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Những trường được đánh dấu (*) không được bỏ trống',
                showCloseButton: true,
                showConfirmButton: false,
            });
            e.preventDefault();
        }
    });

    function validForm() {
        let flag = true,
            hasParent = $('#form-have_parent')[0].checked ?? false,
            parentId = $('#form-parent_id').val() ?? '';

        $('#form-add-edit-store .required').each(function () {
            let val = $(this).val();
            if (!val || val == '' || val == '0') {
                flag = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });

        if (hasParent && !parentId) {
            flag = false
            $('#form-parent_name').addClass('error');
        } else {
            $('#form-parent_name').removeClass('error');
        }

        return flag;
    }

    function validAddress() {
        let flag = true;
        let provinceName = $('#form-province option:selected').attr('data-name').toUpperCase() ?? '',
            districtName = $('#form-district option:selected').attr('data-name').toUpperCase() ?? '',
            wardName = $('#form-ward option:selected').attr('data-name').toUpperCase() ?? '',
            address = $('#form-address').val().toUpperCase() ?? '';

        if (address.includes(provinceName) || address.includes(districtName) || address.includes(wardName)) {
            flag = false;
            $('#form-address').addClass('error');
        } else {
            $('#form-address').removeClass('error');
        }

        return flag;
    }

    $("#form-organization_id").on("change", function () {
        let localityId = $(this).val();
        if (localityId !== "") {
            ajax(ROUTE_LINE_BY_LOCALITY, 'POST', {
                locality_id: localityId
            }).done(function (response) {
                $("#form-line").html(response.htmlString);
            }).fail(function (error) {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        }
    });
})
