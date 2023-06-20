<?php
    use App\Models\Organization;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {!! \App\Helpers\SearchFormHelper::getForm(
                            route('admin.report.print_pxk'),
                            'GET',
                            $indexOptions['searchOptions']
                        ) !!}
                    </div>

                    {!! $table->getTable() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script>
        const ROUTE_GET_AGENCY = "{{ route('admin.get-agency') }}";

        $('#division_id').on('change', function () {
            let currentDivisionId = $(this).val();

            $('.ajax-locality-option').remove();
            $('.ajax-agency-option').remove();

            ajax(ROUTE_GET_LOCALITY, 'POST', {
                division_id: currentDivisionId
            }).done((response) => {
                $("#form-locality_id").append(response.htmlString);
                $("#form-agency_id").append('<option value="" class="ajax-agency-option" selected="">- Đại lý -</option>');
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });

        $('#form-locality_id').on('change', function () {
            let currentLocalityId = $(this).val();

            $('.ajax-agency-option').remove();

            ajax(ROUTE_GET_AGENCY, 'POST', {
                locality_id: currentLocalityId
            }).done((response) => {
                $("#form-agency_id").append(response.htmlString);
            }).fail((error) => {
                console.log(error);
                alert('Server has an error. Please try again!');
            });
        });
    </script>

    <script defer>
        window.onbeforeunload = function (e) {
            if ($('.export-js.disabled').length) {
                return 'Đang xuất dữ liệu, bạn có muốn dừng không?';
            }
        };
        $(document).ready(function () {
            $('.export-js').on('click', function () {
                let _this = $(this)
                    , _wrap = $('.wrapper-progress')
                    , _wrap_error = $('.progress-error', _wrap)
                    , _progress_bar = $('.progress-bar', _wrap)
                    , _progress_info = $('.progress-info', _wrap);
                if (_this.hasClass('disabled')) {
                    return;
                }
                _progress_bar.attr('aria-valuenow', 0);
                _progress_bar.width(`0%`);
                _progress_bar.text(`0%`);
                _progress_info.html(``);

                _this.addClass('disabled');
                _wrap_error.text('');

                let _href = $(this).attr('data-href'),
                    _progress = function (response) {
                        if ('percent' in response) {
                            _progress_bar.attr('aria-valuenow', response['percent']);
                            _progress_bar.width(`${response['percent']}%`);
                            _progress_bar.text(`${response['percent']}%`);
                        }
                        if ('progress_info' in response) {
                            _progress_info.html(response['progress_info']);
                        }
                    },
                    _done = function () {
                        _this.removeClass('disabled');
                    }
                    , _action = function (_form_data) {
                        $.ajax({
                            method: 'post',
                            url: _href,
                            data: _form_data,
                            success: function (response) {
                                _progress(response);
                                if (response['done']) {
                                    _done(response);
                                } else {
                                    setTimeout(function () {
                                        _action(response);
                                    }, 1e3);
                                }
                            },
                            error: function (response) {
                                if ('responseText' in response) {
                                    _wrap_error.text(response['responseText']);
                                }
                                _this.removeClass('disabled');
                            }
                        });
                    };
                _action({});
            });
        });
    </script>
@endpush
