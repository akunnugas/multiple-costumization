@php
    use Modules\Litabmas\Models\PengajuanPendanaanLaporanOutput;

    $emptyStatus = PengajuanPendanaanLaporanOutput::STATUS_BELUM_DINILAI;
    $info = PengajuanPendanaanLaporanOutput::STATUS_OUTPUT[$data['status_output'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant'] ?? null" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
