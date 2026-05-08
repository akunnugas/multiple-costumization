@php
    $permission = request()->permission;
    $isCreated = $data['is_created'];
    $isCanAction = $permission['post'] || $permission['put'];

    if ($isCreated) {
        $createOrUpdateURL = Page::editURL($data['id']);
        if (isset($data['id_indikator_laporan_kinerja'])) {
            $createOrUpdateURL .= '?id_indikator_laporan_kinerja=' . $data['id_indikator_laporan_kinerja'];
        }

        // id_indikator_evaluasi_diri
        if (isset($data['id_indikator_evaluasi_diri'])) {
            $createOrUpdateURL .= '?id_indikator_evaluasi_diri=' . $data['id_indikator_evaluasi_diri'];
        }
    } else {
        $createOrUpdateURL =
            Page::createURL() .
            "?id_unit={$data['id']}&periode_audit={$data['periode_audit']}&id_pengisian_panduan={$data['id_pengisian_panduan']}&id_jadwal_audit={$data['id_jadwal_audit']}";
        if (isset($data['id_indikator_laporan_kinerja'])) {
            $createOrUpdateURL .= '&id_indikator_laporan_kinerja=' . $data['id_indikator_laporan_kinerja'];
        }

        // id_indikator_evaluasi_diri
        if (isset($data['id_indikator_evaluasi_diri'])) {
            $createOrUpdateURL .= '&id_indikator_evaluasi_diri=' . $data['id_indikator_evaluasi_diri'];
        }
    }

@endphp

<x-core::button :href="$createOrUpdateURL" size="xs" :variant="$isCreated || !$isCanAction ? 'outline' : 'primary'" @style([
    'color: #fff' => !$isCreated,
    'color: #466bec; font-weight: 500' => $isCreated || !$isCanAction,
    'border: 1px solid #466bec' => $isCreated || !$isCanAction,
])>
    @if ($isCanAction)
        {{ $isCreated ? 'Ubah' : 'Isi' }} Data
    @else
        Lihat Data
    @endif
</x-core::button>
