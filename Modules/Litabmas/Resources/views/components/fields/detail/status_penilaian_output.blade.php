@php
    use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;

    $emptyStatus = PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN_BELUM_DINILAI;
    $info = PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN[$item['original'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant']" type="secondary" size="sm">
    {{ $info['text'] }}
</x-core::badge>
