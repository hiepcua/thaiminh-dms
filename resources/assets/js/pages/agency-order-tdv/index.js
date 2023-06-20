$(document).ready(function () {
    $('#checkAll').on('change', function () {
        const flag = $(this).is(':checked');
        $('.select-agency-tdv-order').each(function () {
            $(this).prop('checked', flag);
        });
    })

    $('.select-agency-tdv-order').on('change', function () {
        const flag = $(this).is(':checked');

        if(!flag) {
            $('#checkAll').prop('checked', flag);
        }
    })

    $('.check-allow-create-order').on('change', function () {
        let agencyTdvOrderIds = [];
        $('.select-agency-tdv-order').each(function () {
            if ($(this).is(':checked')) {
                agencyTdvOrderIds.push($(this).val())
            }
        });

        if (agencyTdvOrderIds.length === 0) {
            $(".btn-add-agency-order").addClass('disabled');
        } else {
            ajax(ROUTE_CHECK_ALLOW_CREATE_ORDER, 'POST', {
                ids: agencyTdvOrderIds
            })
                .done(async (response) => {
                    if (!response.result) {
                        $(".btn-add-agency-order").addClass('disabled');
                    } else {
                        $(".btn-add-agency-order").removeClass('disabled');
                    }
                }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        }
    })

    $(document).on("click", ".btn-add-agency-order", function () {
        event.preventDefault();
        $("#form-create-agency-order").submit();
    });


    function clearInputSearch()
    {
        let searchAgencyCode = $('#condition-agency-code').val();

        if(searchAgencyCode) {
            $('input.normal-condition').val('');
            $('#searchRange')[0]._flatpickr.clear();
            $('select.normal-condition').val(0);
            $('select.normal-condition.has-select2').val(null).trigger('change');
        }
    }

    clearInputSearch();

    $('#condition-agency-code').on('change', function () {
        clearInputSearch();
    })
})
