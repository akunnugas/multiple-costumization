@php
    use Modules\Litabmas\Models\PenilaianReviewerLaporanProgres;

    $emptyStatus = PenilaianReviewerLaporanProgres::STATUS_LAP_BELUM_DINILAI;
    $info = PenilaianReviewerLaporanProgres::STATUS_LAPORAN_PROGRES_REVIEWER[$data['status_laporan_progres_reviewer'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant'] ?? null" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
