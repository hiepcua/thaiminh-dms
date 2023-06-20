$(document).ready(function () {
    $('.btn-delete-agency').on('click', function () {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn xóa đại lý?',
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
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: error.responseJSON.message,
                        showConfirmButton: false,
                        timer: 3000
                    })
                });
            }
        })
    })

    $('#division_id').on('change', function () {
        let currentDivisionId = $(this).val();

        $('.ajax-locality-option').remove();
        ajax(ROUTE_GET_LOCALITY, 'POST', {
            division_id: currentDivisionId
        }).done((response) => {
            let htmlString = '';
            if(response.htmlString != '') {
                htmlString += response.htmlString;
            }
            $("#form-locality_ids").html(htmlString);
        }).fail((error) => {
            console.log(error);
            alert('Server has an error. Please try again!');
        });
    })
})
