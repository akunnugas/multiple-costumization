@php
    //use model aspek penilaian
    use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;
    $nilaiReviewer = $item['original'] ?? null;

    $nilaiReviewerSum = collect($nilaiReviewer)->sum(function ($nilai) {
        return $nilai->total_nilai ?? 0;
    });

    $countReviewer = count($nilaiReviewer);
    $nilaiReviewerAvg = $countReviewer > 0 ? $nilaiReviewerSum / $countReviewer : 0;

    if ($nilaiReviewerAvg >= AspekPenilaianPresentasiProposal::NILAI_MEMENUHI_KRITERIA) {
        $status = 'Lolos';
        $class = 'status-lolos-success';
    } else {
        $status = 'Tidak Lolos';
        $class = 'status-tidak-lolos-danger';
    }
@endphp

@if (!empty($nilaiReviewer))
    {{ $nilaiReviewerAvg }} <span class="{{ $class }}">({{ $status }})</span>
@endif

@pushOnce('head')
    <style>
        .status-lolos-success {
            color: var(--qn-success-400);
        }

        .status-tidak-lolos-danger {
            color: var(--qn-danger-400);
        }
    </style>
@endPushOnce
