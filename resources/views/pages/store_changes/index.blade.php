@extends('layouts.main')
@section('content')
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!! \App\Helpers\SearchFormHelper::getForm( route('admin.store_changes.index'), 'GET', $indexOptions['searchOptions'] ) !!}
                        </div>
                    </div>

                    @include('snippets.messages')

                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-custom')
    <script defer>
        $('#division_id').on('change', function () {
            let currentDivisionId = $(this).val();

            $('#form-locality_id').empty();
            $('.ajax-tdv-option').remove();

            ajax(ROUTE_GET_LOCALITY, 'POST', {
                division_id: currentDivisionId
            }).done((response) => {
                $("#form-locality_id").html(response.htmlString);
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });
        $('#form-locality_id').on('change', function () {
            let locality_id = $(this).val();
            if(locality_id.length > 0){
                $('.ajax-tdv-option').remove();

                ajax(ROUTE_GET_USER_LOCALITY, 'POST', {
                    'locality_id': locality_id
                }).done((response) => {
                    $("#form-created_by").append(response.htmlString);
                }).fail((error) => {
                    console.log(error);
                    alert('Server has an error. Please try again!');
                });
            }
        });
    </script>
@endpush

