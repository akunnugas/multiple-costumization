@php
    $rekomendasiAnggaran = $item['original'] ?? null;
@endphp

@if(!empty($rekomendasiAnggaran))
    <div class="util_d-flex util_flex-column">
        @foreach($rekomendasiAnggaran as $rekomendasi)
            @php
                $formatted = null;
            @endphp
            @if (!empty($rekomendasi['rekomendasi_anggaran']))
                @php
                    $currency = $rekomendasi['mata_uang'] ?? null;
                    $convert = $currency !== config('money.defaults.currency');
                    $formatted = money($rekomendasi['rekomendasi_anggaran'], $currency, $convert);
                @endphp
            @endif
            <span>
                {{ !empty($formatted) ? $formatted : '-' }} (Reviewer {{ $rekomendasi['reviewer_ke'] }})
            </span>
        @endforeach
    </div>
@endif
