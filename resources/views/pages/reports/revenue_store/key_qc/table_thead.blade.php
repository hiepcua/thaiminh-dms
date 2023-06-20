@foreach($headers as $header)
    <tr>
        @foreach( $header as $item )
            @if(!$item)
                @continue
            @endif
            <th {!! \App\Helpers\Helper::arrayToAttribute($item['attributes'] ?? []) !!}>
                {{ $item['value'] ?? '' }}
            </th>
        @endforeach
    </tr>
@endforeach
