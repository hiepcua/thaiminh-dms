{{ $item->store->name ?? $item->id }}

<button type="button" class="btn btn-icon p-0 tooltipster-store"
        title="
        <div class='store-order-info'>
        <p><span>Địa chỉ:</span><span>{{ $item->store->full_address ?? '-' }}</span></p>
        <p><span>SĐT nhận TT:</span><span>{{ $item->store->phone_owner ?? $item->store->phone_web ?? '-' }}</span></p>
        <p><span>TDV:</span><span>{{ $item->sale?->name ?? '-' }}</span></p>
        <p><span>ASM:</span><span>{{ $item->organization?->parent?->users?->pluck('name')->join(', ') ?? '-' }}</span></p>
        </div>
        ">
    <i data-feather='info' class="text-success"></i>
</button>
