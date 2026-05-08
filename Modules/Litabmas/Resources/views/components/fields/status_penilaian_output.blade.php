@php
    use Modules\Litabmas\Models\PengajuanPendanaanReviewerKegiatan;

    $emptyStatus = PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN_BELUM_DINILAI;
    $info = PengajuanPendanaanReviewerKegiatan::STATUS_PENILAIAN[$value ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant']" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
