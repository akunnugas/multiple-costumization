@php use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres; @endphp

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
            'jenis_laporan' => $data['jenis_laporan_progres'],
            'nama_jenis_laporan' => PengajuanPendanaanLaporanProgres::JENIS_LAP[$data['jenis_laporan_progres']],
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
