$(document).ready(function () {
    $('#checkAll').on('change', function () {
        const flag = $(this).is(':checked');
        $('.select-agency-order').each(function () {
            $(this).prop('checked', flag);
        });
    })

    $('.select-agency-order').on('change', function () {
        const flag = $(this).is(':checked');

        if(!flag) {
            $('#checkAll').prop('checked', flag);
        }
    })

    $('.check-allow-remove-order').on('change', function () {
        let agencyOrderIds = [];
        $('.select-agency-order').each(function () {
            if ($(this).is(':checked')) {
                agencyOrderIds.push($(this).val())
            }
        });

        if (agencyOrderIds.length === 0) {
            $("#btn-remove-order").prop('disabled', false);
        } else {
            ajax(ROUTE_CHECK_ALLOW_DELETE_ORDER, 'POST', {
                ids: agencyOrderIds
            })
                .done(async (response) => {
                    if (!response.result) {
                        $("#btn-remove-order").prop('disabled', true);
                    } else {
                        $("#btn-remove-order").prop('disabled', false);
                    }
                }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        }
    })

    $(document).on('click', '#btn-remove-order', function () {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn hủy đơn hàng?',
            showDenyButton: true,
            confirmButtonText: 'Huỷ',
            denyButtonText: 'Không',
        }).then((result) => {
            if (result.isConfirmed) {
                let agencyOrderIds = [];
                $('.select-agency-order').each(function () {
                    if ($(this).is(':checked')) {
                        agencyOrderIds.push($(this).val())
                    }
                });

                ajax($(this).attr('action-delete'), 'POST', {
                    ids: agencyOrderIds
                })
                    .done(async (response) => {
                        let iconAlert = '';
                        if (response.result) {
                            iconAlert = 'success';
                        } else {
                            iconAlert = 'error';
                        }

                        await Swal.fire({
                            position: 'center',
                            icon: iconAlert,
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        })

                        window.location.reload();
                    }).fail((error) => {
                    console.log(error);
                    alert('Server has an error. Please try again!');
                });
            }
        })
    })
})
