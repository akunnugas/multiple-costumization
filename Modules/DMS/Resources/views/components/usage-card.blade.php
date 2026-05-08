@php
    $endPercent = floor($value / $max * 100);
@endphp

<div class="usage-card__container">
    <div class="card">
        <div class="card__body">
            <h2 class="card__title"><x-core::icon type="cloud" /> Penyimpanan</h2>

            <div class="progress">
                    <div class="progress__bar" data-percent-start="0" data-percent-end="{{ $endPercent }}"></div>
                    <span>{{$endPercent}}%</span>
            </div>

            <p class="usage-card__description">{{$value}} GB tersisa dari {{$max}} GB</p>
        </div>
    </div>
</div>
