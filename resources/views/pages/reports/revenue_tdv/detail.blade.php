@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row flex-row-reverse">
                <div class="col">
                    {!! \App\Helpers\SearchFormHelper::getForm( route('admin.report.revenue.tdv.detail'), 'GET', $indexOptions['searchOptions'] ) !!}
                </div>
            </div>
            @include('snippets.messages')
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        @foreach($header as $item )
                            <th {!! $item['attributes'] ?? '' !!}>
                                {{ $item['value'] ?? '' }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($rows??[] as $row)
                    <tr>
                        @foreach( $row as $item )
                            <td {!! $item['attributes'] ?? '' !!}>
                                @if( $item['link'] ?? '' )
                                    <a href="{{ $item['link'] }}" target="_blank">
                                        {{ $item['value'] ?? '' }}
                                    </a>
                                @else
                                    {{ $item['value'] ?? '' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
