$(document).ready(function () {
    let getAgencyCode = function (codePrefix) {
        ajax(ROUTE_GET_CODE, 'GET', {codePrefix: codePrefix})
            .done((response) => {
                if ('code' in response) {
                    $('#form-code').val(response['code']);
                }
            });
    }
    $('.btn-reload-code').click(function () {
        let _division_id = $('#form-division_id').val();
        if (_division_id) {
            getAgencyCode(_division_id);
        }
    });

    $(document).on('change', '.input-select-all-locality', function () {
        let LocalitySelected = [];
        $('#form-locality_ids').val(LocalitySelected);
        $('#form-locality_ids').trigger('change');
        $("input:checkbox[name=all_locality]:checked").each(function(){
            let parent = $(this).val();
            $('#form-locality_ids').find(`option[data-parent=${parent}]`).map(function (key, option) {
                LocalitySelected.push($(option).attr('value'));
            })
        });

        $('#form-locality_ids').val(LocalitySelected);
        $('#form-locality_ids').trigger('change');
    });

    function handleSelectAllLocality() {
        $('.select-all-locality').remove();
        $('#form-division_id').val().map(function (val) {
            let nameDivision = $("#form-division_id").children(`option[value=${val}]`).first().html();
            $('#set_up_locality_aria').append(`
            <label class="mt-1 me-1 select-all-locality">
                <input class="form-check-input input-select-all-locality" style="width: 18px !important;" type="checkbox" name="all_locality" value="${val}">
                ${nameDivision}
            </label>`)
        })
    }

    handleSelectAllLocality()

    $('#form-division_id').on('change', function () {
        let currentDivisionId = $(this).val();
        handleSelectAllLocality();

        $('.ajax-locality-option').remove();
        ajax(ROUTE_GET_LOCALITY, 'POST', {
            division_id: currentDivisionId
        }).done((response) => {
            $("#form-locality_ids").append(response.htmlString);
        }).fail((error) => {
            console.log(error);
            alert('Server has an error. Please try again!');
        });
    })

    $("#form-locality_ids").on('change', function () {
        ajax(ROUTE_GET_PROVINCE, 'GET', {locality_ids: $(this).val()})
        .done((response) => {
            let provinces = response.provinces;
            $('.new-province-option').remove();
            $("#form-code").val('');
            if ($('#province_for_code').length) {
                Object.keys(provinces).map(function (id) {
                    $('#province_for_code').append(`
                    <option value="${id}" class="new-province-option">${provinces[id]}</option>
                `);
                });
            }
        });

        ajax(ROUTE_GET_USER_LOCALITY, 'POST', {
            'locality_id': $(this).val()
        }).done((response) => {
            $('#form-tdv_user_id option[value!=""]').remove();
            $('#form-tdv_user_id').append(response['htmlString']);
        }).fail((error) => {
            console.log(error);
            alert('Server has an error. Please try again!');
        });
    })

    $("#province_for_code").on('change', function () {
        getAgencyCode($(this).val());
    })
})
