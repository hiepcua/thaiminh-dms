window.organization = {
    ELE_DIVISION: null,
    ELE_LOCALITY: null,
    ELE_USER: null,

    getLocality: function (divisionId) {
        ajax(ROUTE_GET_LOCALITY, 'POST', {
            division_id: divisionId
        }).done((response) => {
            $('.ajax-locality-option').remove();
            $(window.organization.ELE_LOCALITY).append(response.htmlString);
        }).fail((error) => {
            $('.ajax-locality-option[value!=""]').remove();
            alert('Server has an error. Please try again!');
        });
    },
    getUser: function (localityId) {
        ajax(ROUTE_GET_USER_LOCALITY, 'POST', {
            'locality_id': localityId,
        }).done((response) => {
            $(window.organization.ELE_USER).append(response.htmlString);
        }).fail((error) => {
            console.log(error);
            alert('Server has an error. Please try again!');
        });
    },
    divisionChange: function () {
        $(window.organization.ELE_DIVISION).on('change', function () {
            let currentDivisionId = $(this).val();

            if (window.organization.ELE_USER) {
                $('.ajax-tdv-option[value!=""]').remove();
            }

            if (currentDivisionId) {
                window.organization.getLocality(currentDivisionId);
                window.organization.getUser(currentDivisionId);
            } else {
                $('.ajax-locality-option[value!=""]').remove();
            }
        })
    },
    localityChange: function () {
        $(window.organization.ELE_LOCALITY).on('change', function () {
            let localityId = $(this).val();
            $('.ajax-tdv-option[value!=""]').remove();

            window.organization.getUser(localityId);
        })
    }
};
