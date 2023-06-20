<div class="modal fade show" id="store-advanced-search" tabindex="-1" aria-labelledby="store-advanced-search"
     aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body px-sm-2 mx-50 pb-3 pt-2">
                <h3 class="text-left mb-1 modal-title">Tìm kiếm nâng cao</h3>
                <div class="row gy-1 gx-2">
                    <div class="col-12">
                        <label class="form-label" for="number_day_not_order">Số ngày chưa nhập hàng (>=) so với hiện tại</label>
                        <div class="input-group input-group-merge">
                            <input id="number_day_not_order" name="number_day_not_order"
                                   class="form-control add-credit-card-mask"
                                   type="number" min="0" value="{{ $numberDayNotOrder }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <label class="form-label" for="not_enough_visit">Số lần ghé thăm chưa đủ tháng hiện tại</label>
                            <div class="form-check form-switch form-check-primary me-25">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="not_enough_visit"
                                           name="not_enough_visit" value="1" {{ isset($notEnoughVisit) ? 'checked':null }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-center">
                        <button type="button" id="button-submit-store-advanced-search"
                                class="btn btn-primary me-1 mt-1 waves-effect waves-float waves-light">
                            Tìm kiếm
                        </button>
                        <button type="reset" id="button-reset-store-advanced-search"
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
            $('#button-submit-store-advanced-search').click(function () {
                let numberDayNotOrder = $('#number_day_not_order').val(),
                    notEnoughVisit = $('#not_enough_visit:checked').val();
                $('#form-number_day_not_order').val(numberDayNotOrder);
                $('#form-not_enough_visit').val(notEnoughVisit);
                $('.component-search-form').submit();
            })
        })
    </script>
@endpush
