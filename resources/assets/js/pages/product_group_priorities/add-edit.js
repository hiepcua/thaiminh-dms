window.group_product_priority = {};

group_product_priority.input_period_change = () => {
    $('.input-period').on('change', function () {
        let fromPeriod = $('#form-period_from').val();
        let toPeriod = $('#form-period_to').val();

        fromPeriod= new Date(Date.parse(fromPeriod));
        toPeriod= new Date(Date.parse(toPeriod));

        if(toPeriod < fromPeriod && toPeriod != null){
            $("#form-period_from option").eq(0).prop('selected', true);
            $("#form-period_to option").eq(0).prop('selected', true);

            let mes = 'Chu kỳ bắt đầu phải lớn hơn hoặc bằng chu kỳ đến';
            tmp_theme.alert_warning(mes,"Đã hiểu");
            return false;
        }

        rev_period.get_period_products();
    });
}

group_product_priority.input_product_type_change = () => {
    $('#select_product_type').on('change', function () {

        let selectBox = $(this);
        let selectBoxSelected = $(this).val();
        let exists_product_type = $(this).attr('selected_product_type');

        // $("#product_type_value").val(selectBoxSelected);
        //
        // console.log("exists p type : "+exists_product_type);
        //
        // console.log("product type selected : "+selectBoxSelected);

        $( ".option_periods").each(function() { // Process item period
            let productType    = $( this ).attr( "product_type_value" );

            let fromPeriod = $(this).attr("data-from-period");
            let fromPeriodSelected = $(this).attr("data-selected-from-period");

            let toPeriod = $(this).attr("data-to-period");
            let toPeriodSelected = $(this).attr("data-selected-to-period");
            var optionIndex = $(this).attr('index');

            if(selectBoxSelected === productType){
                $( this ).show();
                if(fromPeriod === fromPeriodSelected && fromPeriodSelected){
                    //$("#form-period_from").val(fromPeriodSelected).change();
                    $("#form-period_from option").eq(optionIndex).prop('selected', true);
                }

                if(toPeriod === toPeriodSelected && toPeriodSelected){
                    //$("#form-period_to").val(toPeriodSelected).change();
                    $("#form-period_to option").eq(optionIndex).prop('selected', true);
                }

            }else{
                $( this ).hide();
            }
        });

        if(exists_product_type != selectBoxSelected || exists_product_type == "") {
            $('#form-period_from,#form-period_to').prop("selectedIndex", 0);
        }

        $( ".option_groups").each(function() { // Process item period
            let productType = $(this).attr("product_type_value");
            if(selectBoxSelected === productType){
                $( this ).show();
            }else{
                $( this ).hide();
            }

        });

    });
}

group_product_priority.init = () => {
    group_product_priority.input_product_type_change();
    group_product_priority.input_period_change();
}

$(document).ready(function () {
    group_product_priority.init();
    $('#select_product_type').change();
    $('.btn-delete-priority').on('click', function () {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn xóa nhóm và sản phẩm ưu tiên này không?',
            showDenyButton: true,
            confirmButtonText: 'Xóa',
            denyButtonText: 'Không',
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                ajax($(this).attr('data-action'), 'DELETE', null).done(async (response) => {
                    await Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })

                    window.location.href = ROUTE_DELETE_PRODUCT_GROUP_PRIORITIES;
                }).fail((error) => {
                    console.log(error);
                    alert('Server has an error. Please try again!');
                });
            }
        })
    })

    $('.btn-delete-priorities').on('click', function () {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn xóa nhóm và sản phẩm ưu tiên này không?',
            showDenyButton: true,
            confirmButtonText: 'Xóa',
            denyButtonText: 'Không',
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                ajax($(this).attr('data-action'), 'DELETE', null).done(async (response) => {
                    await Swal.fire({
                        position: 'center',
                        icon: 'success',
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
