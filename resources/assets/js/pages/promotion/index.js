$(document).ready(function () {
    $('.btn-delete-promotion').on('click', function () {
        Swal.fire({
            title: 'Bạn có chắc chắn muốn xóa chương trình quà tặng?',
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
