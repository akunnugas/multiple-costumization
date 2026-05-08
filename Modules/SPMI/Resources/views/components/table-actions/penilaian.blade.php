@php
    $permission = request()->permission;
    $isCanAction = $permission['post'] || $permission['put'];
    $isCreated = $data['apakah_sudah_dibuat'];

    if ($isCreated) {
        $createOrUpdateURL = Page::editURL($data['id']);
    } else {
        $createOrUpdateURL = Page::createURL() . "?id_unit={$data['id']}&periode_audit={$data['periode_audit']}&id_penilaian_panduan={$data['id_penilaian_panduan']}&id_jadwal_audit={$data['id_jadwal_audit']}";
    }
    $userRole = auth()->user()->kode_role;
@endphp


<x-core::button :href="$createOrUpdateURL" size="xs" :variant="($isCreated && !empty($data['total_penilaian_terisi'])) || !$isCanAction ? 'outline' : 'primary'" @style([
    'color: #fff' => !$isCreated || empty($data['total_penilaian_terisi']),
    'color: #466bec; font-weight: 500' => ($isCreated && !empty($data['total_penilaian_terisi'])) || !$isCanAction,
    'border: 1px solid #466bec' => ($isCreated && !empty($data['total_penilaian_terisi'])) || !$isCanAction,
])>
    @if ($userRole === \Modules\Gate\Models\Role::ROLE_AUDITEE)
        Lihat Nilai
    @else
        @if ($isCanAction)
            {{ $isCreated && !empty($data['total_penilaian_terisi']) ? 'Ubah Nilai' : 'Isi Nilai' }}
        @else
            Lihat Nilai
        @endif
    @endif
</x-core::button>
