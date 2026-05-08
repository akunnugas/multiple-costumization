@props([
    'bobotPenilaian' => [],
])
<div class="card">
    <div class="card__header" style="border-bottom: unset; padding-bottom: 0">
        <div class="card__header-left">
            <div class="card__header-block">
                <h2 class="header__title">Keterangan Penilaian</h2>
            </div>
        </div>
    </div>
    <div class="card__body">
        <ul class="util_d-flex" style="width:100%; padding-left: 1.25rem">
            @php
                $width = 100 / count($bobotPenilaian);
                $start = 0;
            @endphp
            @foreach ($bobotPenilaian as $bobot => $label)
                <li class="col" style="width:{{ $width }}%">
                    {{ $start }}-{{ $bobot }} =
                    {{ $label }}</li>
                @php
                    $start = $bobot + 1;
                @endphp
            @endforeach
        </ul>
    </div>
</div>
