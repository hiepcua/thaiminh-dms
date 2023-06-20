<table class="table table-bordered table-hover table-sm">
    <thead class="table-light">
    @foreach($summaryValues['headers'] as $header)
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
    </thead>
    <tbody>
    @foreach($summaryValues['asmRows'] as $rows)
        @foreach($rows as $row)
            <tr>
                @foreach( $summaryValues['rowColumns'] as $col )
                    @php( $item = $row[$col] ?? [] )
                    <td {!! \App\Helpers\Helper::arrayToAttribute($item['attributes'] ?? []) !!}>
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
    @endforeach
    </tbody>
</table>
