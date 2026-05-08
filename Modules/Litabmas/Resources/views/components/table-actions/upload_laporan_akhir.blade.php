@php use Modules\Litabmas\Models\PengajuanPendanaanLaporanAkhir; @endphp

@props([
    'data' => [],
    'canUpdate' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])

@php
    $encoded = base64_encode(
        json_encode([
            'jenis_laporan' => $data['jenis_laporan_akhir'],
            'nama_jenis_laporan' => PengajuanPendanaanLaporanAkhir::JENIS_LAPORAN_OPTIONS[$data['jenis_laporan_akhir']],
        ]),
    );
@endphp

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    @if ($canUpdate)
        <x-core::button leading-icon="arrow-up-tray" variant="outline" size="xs"
                        href="javascript:showModalUpload('{{ $encoded }}')" />
    @else
        <x-core::button leading-icon="arrow-up-tray" variant="outline" size="xs" disabled />
    @endif
</div>
