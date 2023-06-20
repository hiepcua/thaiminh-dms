<div class="modal fade show" id="reason-not-approve" tabindex="-1" aria-labelledby="reason-not-approve"
     aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body px-sm-2 mx-50 pb-3 pt-2">
                <h3 class="text-left mb-1 modal-title">Lý do không duyệt</h3>
                <input type="hidden" id="form-store-change-id" name="store-change-id" value="{{ $storeChangeId }}">
                <div class="row gy-1 gx-2">
                    <div class="col-12">
                        <textarea class="form-control" id="form-reason" name="form-reason" rows="3" maxlength="255"
                                  placeholder="Lý do không duyệt (*)"></textarea>
                    </div>

                    <div class="col-12 text-center">
                        <button type="button" id="button-submit-reason-not-approve"
                                class="btn btn-primary me-1 mt-1 waves-effect waves-float waves-light">
                            Cập nhật
                        </button>
                        <button type="reset" id="button-cancel-reason-not-approve"
                                class="btn btn-outline-secondary mt-1 waves-effect" data-bs-dismiss="modal"
                                aria-label="Close">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts-custom')
    <script defer>
        $(document).ready(function () {
            $('#button-submit-reason-not-approve').click(function () {
                let storeChangeId = $('#form-store-change-id').val(),
                    reason = $('#form-reason').val(),
                    url_not_approve = "{{ route('admin.not-approve-store-changes') }}",
                    url_store_change = "{{ route('admin.store_changes.index') }}";

                if (reason.length === 0 || reason.length > 255) {
                    Swal.fire({text: 'Lý do không duyệt không được bỏ trống.'});
                } else {
                    ajax(url_not_approve, 'POST', {
                        "storeChangeId": storeChangeId,
                        "reason": reason,
                    }).done(function (response) {
                        if (response.htmlString === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Không duyệt thành công',
                                timer: 1500
                            }).then(function(){
                                window.location.href = url_store_change;
                            })
                        } else {
                            Swal.fire({
                                icon: 'error',
                                text: 'Server has an error. Please try again!',
                            })
                        }
                    }).fail(function (error) {
                        console.log(error);
                        alert('Server has an error. Please try again!');
                    });
                }
            })
        })
    </script>
@endpush
