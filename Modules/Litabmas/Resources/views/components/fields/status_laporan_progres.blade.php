@php
    use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;

    $emptyStatus = PengajuanPendanaanLaporanProgres::STATUS_LAP_BELUM_DINILAI;
    $info = PengajuanPendanaanLaporanProgres::STATUS_LAPORAN_PROGRES[$data['status_laporan_progres'] ?? $emptyStatus];
@endphp

<x-core::badge :variant="$info['variant'] ?? null" type="outline" size="sm">
    {{ $info['text'] }}
</x-core::badge>
