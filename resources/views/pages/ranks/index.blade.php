@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    <div class="col ms-auto">
        @include('component.btn-add', ['title' => 'Thêm hạng', 'href' => route('admin.ranks.create')])
    </div>
@endpush
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">

                <div class="card-body" style="padding-bottom:12px;">
                    <div class="row flex-row-reverse">
                        <div class="col">
                        {!!
                            \App\Helpers\SearchFormHelper::getForm(
                                route('admin.ranks.index'),
                                'GET',
                                [
                                    [
                                        "type" => "text",
                                        "name" => "search[name]",
                                        "placeholder" => "Tên hạng",
                                        "defaultValue" => request('search.name'),
                                        'id' => 'searchInput'
                                    ],
                                    [
                                        "type" => "selection",
                                        "name" => "search[status]",
                                        "defaultValue" => request('search.status', null),
                                        "options" => \App\Models\Rank::STATUS_TEXT,
                                    ],
                                ],
                            )
                        !!}
                        </div>
                    </div>
                    <!-- === START: MESSAGES === -->
                    @include('snippets.messages')
                    <!-- === END: MESSAGES === -->

                    <div class="table-responsive">
                        <table id="tableProducts" class="table table-bordered table-striped overflow-hidden">
                            <thead>
                                <tr>
                                    <th class="text-center" width="40px">STT</th>
                                    <th>Tên hạng</th>
                                    <th class="text-center">Mô tả</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center" style="width:12%;min-width:110px;">Chức năng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!$results->isEmpty())
                                    @foreach($results as $item)
                                        <tr>
                                            <td class="text-center">{{ ($loop->index + 1) + ($results->currentPage() - 1) * $results->perPage() }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->desc }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $item->status == \App\Models\Rank::STATUS_ACTIVE ? 'success' : 'secondary' }}">
                                                    {{ $item->status_name }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-sm btn-icon" href="{{ route('admin.ranks.edit', $item->id) }}">
                                                    <i data-feather="edit" class="font-medium-2 text-body"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" style="text-align:center;">Không tìm thấy dữ liệu phù hợp!</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- === START: Pagination === -->
                    <div class="row" style="margin-top:25px;">
                        <div class="col-sm-12 d-flex justify-content-center">
                            {{ !empty($results) ? $results->withQueryString()->links() : '' }}
                            {{-- {{ !empty($results) ? $results->appends(request()->query())->links() : '' }} --}}
                        </div>
                    </div>
                    <!-- === END: Pagination === -->
                </div>
            </div>
        </div>
    </div>

    <!-- === START: MODAL === -->
    {{-- @include('pages.product.modal-add') --}}
    <!-- === END: MODAL === -->

@endsection

@push('scripts-custom')
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        function closeMessage(id){
            setTimeout(() => {
                $('#'+id).css('display', 'none');
            }, 10000);
        }
    </script>
@endpush
