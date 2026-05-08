@php
    $permission = request()->permission;
    $isCanAction = $permission['post'] || $permission['put'];
    $isCreated = $data['apakah_sudah_dibuat'];

    if ($isCreated) {
        $createOrUpdateURL = Page::editURL($data['id']);
    } else {
        $createOrUpdateURL = Page::createURL() . "?id_unit={$data['id']}&periode_audit={$data['periode_audit']}&id_jadwal_audit={$data['id_jadwal_audit']}";
    }

@endphp

<x-core::button :href="$createOrUpdateURL" size="xs" :variant="($isCreated && !empty($data['total_target_terisi'])) || !$isCanAction ? 'outline' : 'primary'" @style([
    'color: #fff' => !$isCreated || empty($data['total_target_terisi']),
    'color: #466bec; font-weight: 500' => ($isCreated && !empty($data['total_target_terisi'])) || !$isCanAction,
    'border: 1px solid #466bec' => ($isCreated && !empty($data['total_target_terisi'])) || !$isCanAction,
])>
    @if ($isCanAction)
        Atur Target Capaian
    @else
        Lihat Nilai
    @endif
</x-core::button>
