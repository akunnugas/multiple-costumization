@props([
    'data' => [],
    'showDetail' => false,
    'canDelete' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    @if($data['status_keaktifan_dan_ordering'] === \Modules\Litabmas\Models\PengumumanPendanaan::STATUS_KLASTER_DIBUKA)
        @php
            $urlCreatePengajuan = route('litabmas.pengajuan-pendanaan.create');
            $urlCreatePengajuan .= '?x_kjp=' . $data['kode_jenis_pendanaan'];
            $urlCreatePengajuan .= '&x_kis=' . $data['id_sumber_pendanaan'];
            $urlCreatePengajuan .= '&x_kid=' . $data['id'];
        @endphp
        <x-core::button leading-icon="eye-solid" variant="outline" size="xs" target="blank"
                        :href="$urlCreatePengajuan"
        />
    @else
        <x-core::button leading-icon="eye-solid" variant="outline" size="xs" disabled
        />
    @endif
</div>
