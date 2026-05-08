@if(!empty($value))
    <span class="info__indikator {{ $value < 0 ? 'down' : '' }}">
        @if($value < 0)
            <img src="{{ asset('images/icon/icon-tren-down.svg') }}" alt="">
        @else
            <img src="{{ asset('images/icon/icon-tren-up.svg') }}" alt="">
        @endif
        <span class="info__indikator-value">{{ \Modules\Core\Helpers\Format::number($value) }}%</span>
    </span>
@endif
