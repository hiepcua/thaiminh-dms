$(document).ready(function () {
    const showTypeCondition = (type) => {
        $('.condition-promotion').hide();
        $(`#${type}`).toggle();
    }

    const changeTypePromotion = function (element) {
        const parent = $(element).parents('.condition-row').first();
        const maxDiscount = parent.find('.max-discount');

        if ($(element).val() == TYPE_HAS_MAX_DISCOUNT) {
            maxDiscount.removeClass('d-none');
        } else {
            maxDiscount.addClass('d-none');
        }
    }

    $(document).on('change', '.type-discount', function () {
        changeTypePromotion($(this))
    });

    // $('.promotion-setup').map(function (key, element) {
    //     let inputSelected = $(element).find('.select-type-condition:checked')[0];
    //     let typeTarget = $(inputSelected).attr('type-condition');
    //     $(element).find(`.template-type`).hide();
    //     $(element).find(`div [data-template-type=${typeTarget}]`).show();
    // })

    $(document).on('change', '.select-type-condition', function () {
        const typeTarget = 'type' + $(this).val();
        $(this).parents('.promotion-setup').find(`.template-type`).addClass('d-none');
        $(this).parents('.promotion-setup').find(`div [data-template-type=${typeTarget}]`).removeClass('d-none');
    })

    const changeTypeIncludeProduct = (element) => {
        const typeInclude = element.val();
        element.parent().find('.include-product').addClass('d-none');

        if (typeInclude) {
            element.parent().find(`.${typeInclude}`).removeClass('d-none');
        }
    }
    $('.select-type-include').map(function () {
        changeTypeIncludeProduct($(this));
    })
    $(document).on('change', '.select-type-include', function () {
        changeTypeIncludeProduct($(this));
    })

    const changeTypeExcludeProduct = (element) => {
        const typeExclude = element.val();
        element.parent().find('.exclude-product').addClass('d-none');

        if (typeExclude) {
            element.parent().find(`.${typeExclude}`).removeClass('d-none');
        }
    }
    $('.select-type-exclude').map(function () {
        changeTypeExcludeProduct($(this));
    })
    $(document).on('change', '.select-type-exclude', function () {
        changeTypeExcludeProduct($(this));
    })

    showTypeCondition(currentType);

    $("input[name=type]").change(function () {
        currentType = $(this).attr('type-condition');
        showTypeCondition(currentType);
    })

    function compare(ar1, ar2) {
        ar1.sort();
        ar2.sort();

        if(ar1.length != ar2.length)
            return false;

        for(var i = 0; i < ar1.length; i++) {
            if (ar1[i] != ar2[i])
                return false;
        }
        return true;
    }

    const registerSelect2 = function () {
        $('.type-promotion-repeater').map(function (key, element) {
            let numberOrder = key + 1;
            $(element).find('.order-type-promotion-repeater').html( "#" + numberOrder);
        })
        $(this).slideDown();

        $('.select2-container').remove();
        $('.has-select2').removeClass('js-select2');
        tmp_theme.select2();
    }

    $('.type-promotion-repeater').map(function (key, element) {
        let numberOrder = key + 1;
        $(element).find('.order-type-promotion-repeater').html( "#" + numberOrder);
    })

    $('.wrap-repeater-promotion:not(.js-repeater)').each(function (i, w) {
        $(w).addClass('js-repeater');
        $(w).repeater({
            show: registerSelect2,
            hide: function (deleteElement) {
                $(this).slideUp(deleteElement);
            },
            repeaters: [{
                show: registerSelect2,
                selector: '.inner-repeater',
                repeaters: [{
                    show: registerSelect2,
                    selector: '.deep-inner-repeater'
                }]
            }]
        });
    });
})
