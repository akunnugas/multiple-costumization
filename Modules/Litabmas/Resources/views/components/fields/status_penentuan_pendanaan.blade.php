@php
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
    $emptyStatus = PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_BELUM_DITENTUKAN;
    $info = PengajuanPendanaanStatus::STATUS_PENENTUAN_PENDANAAN[$data['status_penentuan_pendanaan'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant']" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
