<div class="modal fade" id="searchStore" tabindex="-1" aria-labelledby="shareProjectTitle" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-5 mx-md-50 pb-5">
                <h1 class="text-center mb-1">Tìm kiếm nhà thuốc</h1>
                <!-- form -->
                <form id="form-modalSearchStore" class="gy-1 gx-2 mt-75 has-provinces" onsubmit="return false" novalidate="novalidate">
                    <div class="row">
                        <div class="col-md-3 col-12 mb-1">
                            <input type="text" id="modalStoreName" class="form-control" placeholder="Tên/Mã nhà thuốc">
                        </div>

                        <div class="col-md-3 col-12 mb-1">
                            <select name="province" id="modalProvince" class="form-control has-select2 form-province_id" data-dropdown_parent="#form-modalSearchStore">
                                <option value="">-- Tỉnh/ Thành phố --</option>
                                @if ($provinces)
                                @foreach ($provinces as $item)
                                    <option value="{{ $item->id ?? '' }}">{{ $item->province_name ?? '' }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-md-4 col-12 mb-1">
                            <select name="district" id="modalDistrict" class="form-control has-select2 form-district_id" data-dropdown_parent="#form-modalSearchStore">
                                <option value="">-- Quận/ Huyện --</option>
                            </select>
                        </div>

                        <div class="col-md-2 col-12 mb-1">
                            <button type="button" id="btn-modalSearchStore" class="form-control btn-primary waves-effect waves-float waves-light">Tìm kiếm</button>
                        </div>
                    </div>
                </form>

                <div id="modal-listStore" class="table-responsive"></div>
            </div>
        </div>
    </div>
</div>
@push('scripts-custom')
<script defer>
    $(document).ready(function(){
        let _btn_modal_search_store = $('#btn-modalSearchStore');
        _btn_modal_search_store.on('click', function(){
            let name = $('#modalStoreName').val(),
            province = $('#modalProvince').val(),
            district = $('#modalDistrict').val(),
            url = "{{ route('admin.stores.list-store') }}";
            $.ajax({
                type: "post",
                url: url,
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                data: {
                    'name': name,
                    'province': province,
                    'district': district,
                },
                success: function (response) {
                    $('#modal-listStore').html(response);
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        });

        $(document).on('click', '.model-selectStore', function(){
            let id = $(this).attr('data-storeId'),
            code = $(this).attr('data-storeCode'),
            name = $(this).attr('data-storeName'),
            desc = code+'-'+name;

            $('#form-parent_id').val(id);
            $('#form-parent_name').val(desc);

            // Đóng modal
            $('#searchStore .btn-close').click();
        });
    });
</script>
@endpush
