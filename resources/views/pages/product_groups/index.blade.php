@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    @can('them_nhom_san_pham')
        <div class="col ms-auto">
            @include('component.btn-add', ['title' => 'Thêm mới', 'href' => route('admin.product-groups.create')])
        </div>
    @endcan
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            {!!
                                \App\Helpers\SearchFormHelper::getForm(
                                    route('admin.product-groups.index'),
                                    'GET',
                                    [
                                        [
                                            "type" => "text",
                                            "id" => "searchInput",
                                            "name" => "search[name]",
                                            "defaultValue" => request('search.name'),
                                            "placeholder" => 'Tên nhóm SP ',
                                        ],
                                    ]
                                )
                            !!}
                        </div>
                    </div>
                </div>

                {!! $table->getTable() !!}
            </div>
        </div>
    </div>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.btn-delete-product-group').on('click', function () {
                Swal.fire({
                    title: 'Bạn có chắc chắn muốn xóa nhóm sản phẩm ?',
                    showDenyButton: true,
                    confirmButtonText: 'Xóa',
                    denyButtonText: 'Không',
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        ajax($(this).attr('data-action'), 'DELETE', null).done(async (response) => {
                            await Swal.fire({
                                position: 'center',
                                icon: response.icon,
                                title: response.message,
                                showConfirmButton: false,
                                showCloseButton: false,
                            }).then(() => {
                                window.location.reload();
                            });
                        }).fail((error) => {
                            console.log(error);
                            alert('Server has an error. Please try again!');
                        });
                    }
                })
            })
        });
    </script>
@endpush
