@php
    $nilaiKomposisi = $item['original'] ?? null;
    $rataRataNilai = null;
    if (!empty($nilaiKomposisi)) {
        foreach ($nilaiKomposisi as $nilai) {
            $rataRataNilai += $nilai['total_nilai_komposisi_reviewer'];
        }
    }

    $rataRataNilai = !empty($nilaiKomposisi)
        ? $rataRataNilai / count($nilaiKomposisi)
        : null;
@endphp

<div class="util_d-flex util_flex-column">
    <span>
        {{ $rataRataNilai }}
    </span>
    @if(!empty($nilaiKomposisi))
        @foreach($nilaiKomposisi as $value)
            @php
                $nilai = !empty($value['total_nilai_komposisi_reviewer'])
                    ? \Modules\Core\Helpers\Format::removeTrailingZeroes($value['total_nilai_komposisi_reviewer'])
                    : '-';
            @endphp
            <span>
                {{ $nilai }} (Reviewer {{ $value['reviewer_ke'] }} - {{ $value['nama'] }})
            </span>
        @endforeach
    @endif
</div>
