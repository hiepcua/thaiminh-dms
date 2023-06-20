<?php
    use App\Models\Organization;
?>
@extends('layouts.main')
@section('page_title', $page_title)
@push('content-header')
    @can('them_dai_ly')
        <div class="col ms-auto">
            @include('component.btn-add', ['title'=>'Thêm mới', 'href'=>route('admin.agency.create')])
        </div>
    @endcan
@endpush
@section('content')
    <section class="app-user-list">
        <div class="row" id="table-striped">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row flex-row-reverse">

                            <div class="col">
                                <?php
                                    $localities = [
                                        0 => "- Địa bàn -"
                                    ];
                                    foreach(($formOptions['locality_ids'] ?? []) as $item_locality) {
                                        $localities[$item_locality->id] = $item_locality->name;
                                    }
                                ?>
                                {!!
                                    \App\Helpers\SearchFormHelper::getForm(
                                        route('admin.agency.index'),
                                        'GET',
                                        [
                                            [
                                                "type" => "text",
                                                "name" => "search[codeOrName]",
                                                "placeholder" => "Mã/Tên đại lý",
                                                "defaultValue" => request('search.codeOrName'),
                                            ],
                                            [
                                                "type" => "divisionPicker",
                                                "divisionPickerConfig" => [
                                                    "currentUser" => \App\Helpers\Helper::currentUser(),
                                                    "hasRelationship" => true,
                                                    "activeTypes" => [
                                                        Organization::TYPE_KHU_VUC,
                                                    ],
                                                    "excludeTypes" => [
                                                        Organization::TYPE_DIA_BAN
                                                    ],
                                                    "setup" => [
                                                        'multiple' => false,
                                                        'name' => 'search[division_id]',
                                                        'class' => '',
                                                        'id' => 'division_id',
                                                        'attributes' => '',
                                                        'selected' => request('search.division_id', null)
                                                    ]
                                                ],
                                            ],
                                            [
                                                "type" => "selection",
                                                "name" => "search[locality_ids]",
                                                "defaultValue" => request('search.locality_ids', null),
                                                "options" => $localities,
                                                "id" => "form-locality_ids"
                                            ],
                                            [
                                                "type" => "checkbox",
                                                "name" => "search[userIsAgency]",
                                                "id" => "userIsAgency",
                                                "defaultValue" => request('search.userIsAgency'),
                                                "options" => \App\Models\AgencyOrder::STATUS_TEXTS,
                                                "placeholder" => "User là đại lý"
                                            ],
                                        ],
                                    )
                                !!}
                            </div>
                        </div>
                    </div>

                    {!! $agenciesTable->getTable() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts-custom')
    <script src="{{ asset('vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/core/pages/agency/index.js') }}"></script>
@endpush
