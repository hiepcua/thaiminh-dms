window.rev_period = {};
rev_period.get_period_products = () => {
    let _from = $('select[name="period_from"]').val(),
        _to = $('select[name="period_to"]').val();
        _product_type = $('#select_product_type').val();

    //select_products
    $.get(product_period_url, {'from': _from, 'to': _to,'product_type':_product_type}, function (response) {
        // $(".row_groups").each(function( index ) {
        //     $(this).find('.select_products').hide();
        // });

        //foreach json
        // $.each(response,function(groupId, subGroups){
        //     console.log("group id "+ groupId);
        //     $.each(subGroups,function(subGroupId, _products){
        //         console.log("sub id "+ subGroupId);
        //         $.each(_products,function(_productId, _productName){
        //             console.log("product id "+ _productId + " | name : "+_productName);
        //         });
        //     });
        //
        // });

    });
};

rev_period.input_period_change = () => {
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


rev_period.input_product_type_change = () => {
    $('#select_product_type').on('change', function () {

        let selectBox = $(this);
        let selectBoxSelected = $(this).val();
        let exists_product_type = $(this).attr('selected_product_type');

        // $("#product_type_value").val(selectBoxSelected);
        //
        //alert(123);
        console.log("exists p type : "+exists_product_type);
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


            $(".row_groups").each(function () { // Process item group
                let selectProductType = $(this).attr('product_type');
                if (selectBoxSelected == selectProductType) {
                    $(this).show();
                    $(this).find('input,select').prop("disabled", false);
                } else {
                    $(this).hide();
                    $(this).find('input,select').prop("disabled", true);
                }

                if(exists_product_type){
                    $(this).show();
                    $(this).find('input,select').prop("disabled", false);
                }
            });


        if(exists_product_type != selectBoxSelected || exists_product_type == "") {
            $('#form-period_from,#form-period_to').prop("selectedIndex", 0);
        }
    });
}

rev_period.input_discount_rate = () => {
    let calc_total_rate = function () {
        $('table tbody tr').each(function (_i, _tr) {
            let _total_rate = 0;
            $('.input-discount-rate[type="number"]', _tr).each(function (_ii, _ele) {
                _total_rate += parseFloat($(_ele).val() || 0);
            });
            $('.row-total span', _tr).text(_total_rate);
        });
    };
    calc_total_rate();
    $('.input-discount-rate[type="number"]').on('keyup', function () {
        calc_total_rate();
    });
}

rev_period.init = () => {
    rev_period.input_period_change();
    rev_period.input_discount_rate();
    rev_period.input_product_type_change();
}

$(document).ready(function () {
    rev_period.init();
    $('#select_product_type').change();
});
