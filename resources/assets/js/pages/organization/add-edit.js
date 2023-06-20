let type_change = function (_type_val) {
    if (typeof _type_val == 'undefined') {
        _type_val = $('#form-type').val();
    }
    _type_val = parseInt(_type_val);

    let _parent_ele = $('#form-parent_id');
    _parent_ele.val('');
    _parent_ele.trigger('change')

    $('option', _parent_ele).each(function (_i, _e) {
        let _opt_val = parseInt($(_e).val());
        $(_e).removeClass('d-none');
        if (_type_val === TYPE_KHAC) {
            $(_e).prop('disabled', !!_opt_val);
            $(_e).addClass('d-none');
        } else {
            let _ele_type = $(_e).data('type');
            if (typeof _ele_type != 'undefined') {
                $(_e).prop('disabled', (_ele_type !== (_type_val - 1)));
                if (_ele_type !== (_type_val - 1)) {
                    $(_e).addClass('d-none');
                }
            }
        }
    });
};
$('#form-type').on('change', function () {
    let _val = $(this).val();
    type_change(_val);
});
type_change();
//
$('#btn-form-submit').on('click', function () {
    let _form = $('#add-edit-organization')
        , _ele_status = $('#form-status', _form)
        , _status = parseInt(_ele_status.val())
        , _old_status = _ele_status.data('old')
        , _name = $('#form-name').val();

    let _alert = typeof _old_status != 'undefined' && parseInt(_old_status) === 1 && _status !== 1;
    if (_alert) {
        Swal.fire({
            title: 'Bạn có muốn thay đổi không?',
            html: `Khi đổi trạng thái <b>"${_name}"</b> sang <b>KHÔNG HOẠT ĐỘNG</b> thì các cấp con của cấp này cũng chuyển sang là <b>KHÔNG HOẠT ĐỘNG</b>.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Cập nhật',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.isConfirmed) {
                _form.submit();
            }
        })
    } else {
        _form.submit();
    }
});
