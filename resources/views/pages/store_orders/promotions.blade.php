@foreach( $promotions as $_promotion )
    <div class="accordion-item promo-item promo-{{ $_promotion->id }}">
        <div class="promo-item-border">
            <h5 class="accordion-header" id="heading{{ $_promotion->id }}">
                <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#accordion{{ $_promotion->id }}"
                    aria-expanded="true"
                    aria-controls="accordion{{ $_promotion->id }}"
                >
                    <span>
                    {{ $_promotion->name }}
                        @if($_promotion->desc)
                            <span class="fw-normal d-block">{{ $_promotion->desc }}</span>
                        @endif
                    </span>
                </button>
            </h5>
            <div
                id="accordion{{ $_promotion->id }}"
                class="accordion-collapse collapse show {{--{{ ($promotion_values[$_promotion->id] ?? '') ? 'show' : '' }}--}}"
                aria-labelledby="heading{{ $_promotion->id }}"
            >
                <div class="accordion-body ">
                    <div class="promotion pb-1" data-promo_id="{{ $_promotion->id }}" data-number="0">
                        @foreach( $_promotion->promotionConditions as $condition )
                            <div
                                class="{{ $_promotion->auto_apply == \App\Models\Promotion::AUTO_APPLY ? 'readonly' : '' }}">
                                <label class="form-check promotion-condition">
                                    <input type="checkbox" class="form-check-input"
                                           value="{{ $_promotion->id }}_{{ $condition->id }}"
                                        {{ in_array( $condition->id, ( $promotion_checked[$_promotion->id] ?? [] ) ) ? 'checked' : '' }}>
                                    <span>{{ $condition->name }}</span>
                                </label>
                                <div id="promo-info-{{ $_promotion->id }}-{{ $condition->id }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="promotions[{{ $_promotion->id }}]"
                           value="{{ $promotion_values[$_promotion->id] ?? '' }}"
                           class="promotion-value">
                </div>
            </div>
        </div>
    </div>
@endforeach
