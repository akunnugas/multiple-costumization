@php
    $nilaiReviewer = $item['original'] ?? null;

    $nilaiReviewerSum = collect($nilaiReviewer)->sum(function ($nilai) {
        return $nilai->total_nilai ?? 0;
    });

    $countReviewer = count($nilaiReviewer);
    $nilaiReviewerAvg = $countReviewer > 0 ? $nilaiReviewerSum / $countReviewer : 0;
@endphp

@if (!empty($nilaiReviewer))
    <div class="util_d-flex util_flex-column">
        <span>
            {{ $nilaiReviewerAvg }}
        </span>
        @foreach ($nilaiReviewer as $nilai)
            @php
                $nilaiToDisplay = $nilai->total_nilai;
                $formatted = Format::removeTrailingZeroes($nilaiToDisplay);
            @endphp
            <span>
                {{ !empty($formatted) ? $formatted : '0' }} (Reviewer {{ $nilai->reviewer_ke }} -
                {{ $nilai->nama_user }})
            </span>
        @endforeach
    </div>
@endif
