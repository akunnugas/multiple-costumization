@php
    use Modules\Litabmas\Models\PengajuanPendanaanReviewerAdministrasi;

    $emptyStatus = PengajuanPendanaanReviewerAdministrasi::STATUS_PENILAIAN_BELUM_DINILAI;
    $info = PengajuanPendanaanReviewerAdministrasi::STATUS_PENILAIAN[$item['original'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant']" type="secondary" size="sm">
    {{ $info['text'] }}
</x-core::badge>
